<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Prestashop in the file LICENSES.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before your update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      Tech Support <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2017 MultiSafepay, Inc. (http://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use MultiSafepay\PrestaShop\helpers\Helper;
use MultiSafepay\PrestaShop\models\Api\MspClient;

class MultiSafepayPaymentModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $display_column_left = false;

    public function postProcess()
    {
        if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 || $this->context->cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
        }

        $customer = new Customer($this->context->cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
        }

        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

        $shipping = new Address($this->context->cart->id_address_delivery);
        $shipping_country = new Country($shipping->id_country);
        $billing = new Address($this->context->cart->id_address_invoice);
        $billing_country = new Country($billing->id_country);
        $currency = new Currency($this->context->cart->id_currency);

        $lang_iso = Language::getIsoById($this->context->cart->id_lang);
        $locale = Language::getLocaleByIso($lang_iso);
        $real_locale = str_replace('-', '_', $locale);

        $ip_address = $this->validateIP($_SERVER['REMOTE_ADDR']);
        $forwarded_ip = $this->validateIP($_SERVER['HTTP_X_FORWARDED_FOR']);

        list ($items, $shopping_cart, $checkout_options) = $this->getShoppingCart();
        list ($street_billing, $house_number_billing) = $this->parseAddress($billing->address1, $billing->address2);
        list ($street_shipping, $house_number_shipping) = $this->parseAddress($shipping->address1, $shipping->address2);

        list ($type, $gateway_info) = $this->getTypeAndGatewayInfo($customer);

        $transaction_data = array(
            "type" => $type,
            "order_id" => $this->context->cart->id,
            "currency" => $currency->iso_code,
            "amount" => round($this->context->cart->getOrderTotal(true, Cart::BOTH), 2) * 100,
            "description" => $this->module->l('Order of Cart: ', 'payment') . $this->context->cart->id,
            "recurring_id" => $this->decryptRecurringId(),
            "var1" => "",
            "var2" => "",
            "var3" => "",
            "manual" => "false",
            "items" => $items,
            "gateway" => Tools::getValue('gateway'),
            "seconds_active" => $this->getSecondsActive(),
            "payment_options" => array(
                "notification_url" => $this->context->link->getModuleLink($this->module->name, 'validation', array("key" => $this->context->customer->secure_key, "id_module" => $this->module->id, "type" => "notification"), true),
                "redirect_url" => $this->context->link->getModuleLink($this->module->name, 'validation', array("key" => $this->context->customer->secure_key, "id_module" => $this->module->id, "type" => "redirect"), true),
                "cancel_url" => $this->context->link->getPageLink('order', true, null, array('step' => '3')),
                "close_window" => "true"
            ),
            "customer" => array(
                "locale" => $real_locale,
                "ip_address" => $ip_address,
                "forwarded_ip" => $forwarded_ip,
                "first_name" => $billing->firstname,
                "last_name" => $billing->lastname,
                "address1" => $street_billing,
                "address2" => $billing->address2,
                "house_number" => $house_number_billing,
                "zip_code" => $billing->postcode,
                "city" => $billing->city,
                "country" => $billing_country->iso_code,
                "phone" => $billing->phone,
                "email" => $customer->email
            ),
            "delivery" => array(
                "locale" => $real_locale,
                "ip_address" => $ip_address,
                "forwarded_ip" => $forwarded_ip,
                "first_name" => $shipping->firstname,
                "last_name" => $shipping->lastname,
                "address1" => $street_shipping,
                "address2" => $shipping->address2,
                "house_number" => $house_number_shipping,
                "zip_code" => $shipping->postcode,
                "city" => $shipping->city,
                "country" => $shipping_country->iso_code,
                "phone" => $shipping->phone,
                "email" => $customer->email,
            ),
            "shopping_cart" => $shopping_cart,
            "checkout_options" => $checkout_options,
            "gateway_info" => $gateway_info,
            "plugin" => array(
                "shop" => 'Prestashop',
                "shop_version" => _PS_VERSION_,
                "plugin_version" => ' - Plugin 4.6.1',
                "partner" => "MultiSafepay",
            )
        );

        if (Tools::getValue('gateway') == "banktrans") {
            $transaction_data['customer']['disable_send_email'] = true;
        }
        if (Configuration::get('MULTISAFEPAY_DEBUG')) {
            $logger = new FileLogger(0);
            $logger->setFilename(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'logs/multisafepay_cart_' . $this->context->cart->id . '.log');
            $logger->logDebug("Request data -------------------------");
            $logger->logDebug($transaction_data);
        }

        try {
            $this->setTokenization($customer, $this->context->cart);

            $result = $multisafepay->orders->post($transaction_data);
            if (Configuration::get('MULTISAFEPAY_DEBUG')) {
                $logger = new FileLogger(0);
                $logger->setFilename(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'logs/multisafepay_cart_' . $this->context->cart->id . '.log');
                $logger->logDebug("Response -------------------------");
                $logger->logDebug($result);
            }

            if (!empty($multisafepay->orders->result->error_code)) {
                //When a consumer cancels the order, the cart is still active and this will cause the cart->id to be used when placing a new transaction. For these situations we will duplicate the cart and use the new cart->id once if a 1006 transaction exists error occurs.
                if ($multisafepay->orders->result->error_code == '1006') {
                    $new_cart = $this->context->cart->duplicate();
                    if (!$new_cart || !Validate::isLoadedObject($new_cart['cart'])) {
                        $this->errors[] = Tools::displayError($this->module->l('Transaction request failed because the cart ID was already used and we couldn\'t create a new cart.', 'payment'));
                    } elseif (!$new_cart['success']) {
                        $this->errors[] = Tools::displayError($this->module->l('Transaction request failed because the cart ID was already used and we couldn\'t create a new cart because the products are no longer available.', 'payment'));
                    } else {
                        //Remove the old cart as this can't be used for a MultiSafepay transaction request anymore.
                        $this->context->cart->delete();

                        //SET the new cart active in context
                        $this->context->cookie->id_cart = $new_cart['cart']->id;
                        $context = $this->context;
                        $context->cart = $new_cart['cart'];
                        CartRule::autoAddToCart($context);
                        $this->context->cookie->write();

                        //Update the transaction data with the new cart ID
                        $transaction_data['order_id'] = $new_cart['cart']->id;

                        $result = $multisafepay->orders->post($transaction_data);
                        if (!empty($multisafepay->orders->result->error_code)) {
                            $this->errors[] = $this->module->l('There was an error processing your transaction request, please try again with another payment method. Error: ', 'payment') . $multisafepay->orders->result->error_code . ' - ' . $multisafepay->orders->result->error_info;
                            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, array('step' => '3')));
                        } else {
                            //For bank transfer we use a direct transaction, this means we do not redirect to Multisafepay. We use the default wire transfer email from PrestaShop and provide the payment data.
                            if (Tools::getValue('gateway') == "banktrans") {
                                $mailVars = array(
                                    '{bankwire_owner}' => $result->gateway_info->destination_holder_name,
                                    '{bankwire_details}' => $result->gateway_info->destination_holder_iban,
                                    '{bankwire_address}' => $this->module->l('Payment reference : ', 'payment') . $result->gateway_info->reference
                                );

                                $helper = new Helper;
                                $helper->saveBankTransferDetails($result->gateway_info, $this->context->cart->id);
                                $used_payment_method = $helper->getPaymentMethod($multisafepay->orders->result->data->payment_details->type);

                                $this->module->validateOrder((int)$new_cart['cart']->id, Configuration::get('PS_OS_BANKWIRE'), $this->context->cart->getOrderTotal(true, Cart::BOTH), $used_payment_method, null, $mailVars, (int)$currency->id, false, $customer->secure_key);
                                Tools::redirect($this->context->link->getModuleLink($this->module->name, 'validation', array("key" => $this->context->customer->secure_key, "id_module" => $this->module->id, "type" => "redirect", "transactionid" => $new_cart['cart']->id), true));
                                exit;
                            } else {
                                Tools::redirectLink($multisafepay->orders->getPaymentLink());
                            }
                        }
                    }
                }
                $this->errors[] = $this->module->l('There was an error processing your transaction request, please try again with another payment method. Error: ', 'payment') . $multisafepay->orders->result->error_code . ' - ' . $multisafepay->orders->result->error_info;
                $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, array('step' => '3')));
            } else {
                //For bank transfer we use a direct transaction, this means we do not redirect to Multisafepay. We use the default wire transfer email from PrestaShop and provide the payment data.
                if (Tools::getValue('gateway') == "banktrans") {
                    $mailVars = array(
                        '{bankwire_owner}' => $result->gateway_info->destination_holder_name,
                        '{bankwire_details}' => $result->gateway_info->destination_holder_iban,
                        '{bankwire_address}' => $this->module->l('Payment reference : ', 'payment') . $result->gateway_info->reference
                    );

                    $helper = new Helper;
                    $helper->saveBankTransferDetails($result->gateway_info, $this->context->cart->id);
                    $used_payment_method = $helper->getPaymentMethod($multisafepay->orders->result->data->payment_details->type);

                    $this->module->validateOrder((int)$this->context->cart->id, Configuration::get('PS_OS_BANKWIRE'), $this->context->cart->getOrderTotal(true, Cart::BOTH), $used_payment_method, null, $mailVars, (int)$currency->id, false, $customer->secure_key);
                    Tools::redirect($this->context->link->getModuleLink($this->module->name, 'validation', array("key" => $this->context->customer->secure_key, "id_module" => $this->module->id, "type" => "redirect", "transactionid" => $this->context->cart->id), true));
                    exit;
                } else {
                    Tools::redirectLink($multisafepay->orders->getPaymentLink());
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, array('step' => '3')));
        }
    }


    /**
     * @param $ipAddress
     * @return string|null
     */
    private function validateIP($ipAddress)
    {
        if (isset($ipAddress)) {
            $ipList = explode(',', $ipAddress);
            $ipAddress = trim(reset($ipList));

            if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                return $ipAddress;
            }
        }
        return null;
    }

    /*
     * getCart() generated the checkout data structure and items list
     */

    public function getSecondsActive()
    {
        $seconds_active = null;
        $seconds = Configuration::get('MULTISAFEPAY_TIME_ACTIVE');
        $timeUnit = Configuration::get('MULTISAFEPAY_TIME_UNIT');

        if (empty($seconds)) {
            return $seconds_active;
        }

        switch ($timeUnit) {
            case 'days':
                $seconds_active = $seconds * 24 * 60 * 60;
                break;
            case 'hours':
                $seconds_active = $seconds * 60 * 60;
                break;
            case 'seconds':
                $seconds_active = $seconds;
                break;
            default:
                $seconds_active = null;
        }

        return $seconds_active;
    }


    private function getShoppingCart()
    {
        $cart = $this->context->cart;
        $total_data = $cart->getSummaryDetails();

        $shopping_cart = array();
        $checkout_options = array();
        $checkout_options['tax_tables']['default'] = array('shipping_taxed' => 'true', 'rate' => '0.21');
        $checkout_options['tax_tables']['alternate'][] = '';

        // Products
        $items = "<ul>\n";
        $products = array_merge($total_data['products'], $total_data['gift_products']);

        foreach ($products as $product) {
            $product_name = $product['name'];
            $merchant_item_id = $product['id_product'];
            $tax_name = $product['tax_name'] ?: 'none';

            if (!empty($product['attributes_small'])) {
                $product_name .= ' ( ' . $product['attributes_small'] . ' )';
                $merchant_item_id .= '-' . $product['id_product_attribute'];
            }

            $items .= "<li>" . $product['cart_quantity'] . ' x : ' . $product_name . "</li>\n";

            $shopping_cart['items'][] = array(
                'name' => $product_name,
                'description' => $product['description_short'],
                'unit_price' => round($product['price'], 10),
                'quantity' => $product['quantity'],
                'merchant_item_id' => $merchant_item_id,
                'tax_table_selector' => $tax_name,
                'weight' => array('unit' => $product['weight'],
                    'value' => 'KG')
            );
            $checkout_options['tax_tables']['alternate'][] =
                array('name' => $tax_name, 'rules' => array(array('rate' => $product['rate'] / 100)));
        }

        $items .= "</ul>\n";

        // Fee
        if (isset($cart->feeamount) && $cart->feeamount > 0) {
            $shopping_cart['items'][] = array(
                'name' => 'Fee',
                'description' => $this->module->l('Fee', 'payment'),
                'unit_price' => $cart->feeamount,
                'quantity' => 1,
                'merchant_item_id' => 'Fee',
                'tax_table_selector' => 'Fee',
                'weight' => array('unit' => 0, 'value' => 'KG')
            );
            $checkout_options['tax_tables']['alternate'][] =
                array('name' => 'Fee', 'rules' => array(array('rate' => '0.00')));
        }

        // Discount
        if ($total_data['total_discounts'] > 0) {
            $shopping_cart['items'][] = array(
                'name' => 'Discount',
                'description' => $this->module->l('Discount', 'payment'),
                'unit_price' => round(-$total_data['total_discounts'], 10),
                'quantity' => 1,
                'merchant_item_id' => 'Discount',
                'tax_table_selector' => 'Discount',
                'weight' => array('unit' => 0, 'value' => 'KG')
            );
            $checkout_options['tax_tables']['alternate'][] =
                array('name' => 'Discount', 'rules' => array(array('rate' => '0.00')));
        }

        // Wrapping
        if ($total_data['total_wrapping'] > 0) {
            $shopping_cart['items'][] = array(
                'name' => 'Wrapping',
                'description' => $this->module->l('Wrapping', 'payment'),
                'unit_price' => round($total_data['total_wrapping_tax_exc'], 10),
                'quantity' => 1,
                'merchant_item_id' => 'Wrapping',
                'tax_table_selector' => 'Wrapping',
                'weight' => array('unit' => 0, 'value' => 'KG')
            );
            $wrapping_tax_percentage = round(($total_data['total_wrapping'] - $total_data['total_wrapping_tax_exc']) * ($total_data['total_wrapping_tax_exc'] / 100), 2);
            $checkout_options['tax_tables']['alternate'][] =
                array('name' => 'Wrapping', 'rules' => array(array('rate' => $wrapping_tax_percentage)));
        }

        // Shipping
        if ($total_data['total_shipping'] > 0) {
            $shopping_cart['items'][] = array(
                'name' => 'Shipping',
                'description' => $this->module->l('Shipping', 'payment'),
                'unit_price' => round($total_data['total_shipping_tax_exc'], 10),
                'quantity' => 1,
                'merchant_item_id' => 'msp-shipping',
                'tax_table_selector' => 'Shipping',
                'weight' => array('unit' => 0, 'value' => 'KG')
            );
            $shipping_tax_percentage = round(($total_data['total_shipping'] - $total_data['total_shipping_tax_exc']) / ($total_data['total_shipping_tax_exc']), 2);
            $checkout_options['tax_tables']['alternate'][] =
                array('name' => 'Shipping', 'rules' => array(array('rate' => $shipping_tax_percentage)));
        }

        return array($items, $shopping_cart, $checkout_options);
    }

    /**
     * Split the address into street and house number with extension.
     *
     * @param string $address1
     * @param string $address2
     * @return array
     */
    private function parseAddress($address1, $address2 = '')
    {
        // Trim the addresses
        $address1 = trim($address1);
        $address2 = trim($address2);
        $fullAddress = trim("{$address1} {$address2}");
        $fullAddress = preg_replace("/[[:blank:]]+/", ' ', $fullAddress);

        // Make array of all regex matches
        $matches = array();

        /**
         * Regex part one: Add all before number.
         * If number contains whitespace, Add it also to street.
         * All after that will be added to apartment
         */
        $pattern = '/(.+?)\s?([\d]+[\S]*)(\s?[A-z]*?)$/';
        preg_match($pattern, $fullAddress, $matches);

        //Save the street and apartment and trim the result
        $street = isset($matches[1]) ? $matches[1] : '';
        $apartment = isset($matches[2]) ? $matches[2] : '';
        $extension = isset($matches[3]) ? $matches[3] : '';
        $street = trim($street);
        $apartment = trim($apartment . $extension);

        return array($street, $apartment);
    }


    private function getTypeAndGatewayInfo($customer)
    {
        switch (Tools::getValue('gateway')) {
            case 'ideal':
                $type = 'direct';
                $gateway_info = array(
                    "issuer_id" => Tools::getValue('issuer')
                );
                break;
            case 'afterpay':
            case 'payafter':
            case 'einvoice':
                $type = 'direct';
                $gateway_info = array(
                    'birthday' => Tools::getValue('birthday'),
                    'bankaccount' => Tools::getValue('bankaccount'),
                    'phone' => Tools::getValue('phone'),
                    'gender' => Tools::getValue('gender'),
                    'email' => $customer->email
                );
                break;
            case 'banktrans':
            case 'ing':
            case 'kbc':
            case 'paypal':
            case 'santander':
            case 'trustly':
                // No additional data needed
                $type = 'direct';
                $gateway_info = array();
                break;
            case 'amex':
            case 'visa':
            case 'mastercard':
                if ($this->decryptRecurringId() !== false) {
                    $type = 'direct';
                    $gateway_info = array();
                } else {
                    $type = 'redirect';
                    $gateway_info = array();
                }
                break;
            default:
                $type = 'redirect';
                $gateway_info = array();
                break;
        }
        return (array($type, $gateway_info));
    }

    private function decryptRecurringId()
    {
        $recurringId = pSQL(Tools::getValue('saved_cc'));
        return PhpEncryptionCore::decrypt($recurringId);
    }


    private function setTokenization($customer_data, $cart_data)
    {
        $creditcard_input = Tools::getValue('creditcard-input');
        if (!empty($creditcard_input) && !$this->isExistingRecurring($customer_data->id, $creditcard_input)) {
            Db::getInstance()->insert('multisafepay_tokenization', array(
                'customer_id' => $customer_data->id,
                'order_id' => $cart_data->id,
                'cc_name' => pSQL($creditcard_input),
            ));
        }
    }

    private function isExistingRecurring($customer_id, $creditcard_input)
    {
        $sql = new DbQuery();
        $sql->select('id')
            ->from('multisafepay_tokenization')
            ->where("customer_id = {$customer_id} AND cc_name = '" . pSQL($creditcard_input) . "'");

        return Db::getInstance()->executeS($sql);
    }
}
