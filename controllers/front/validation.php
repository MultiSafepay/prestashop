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

class MultisafepayValidationModuleFrontController extends ModuleFrontController
{

    private $timeToWait = 5;
    protected $lock_file;
    protected $transaction;
    protected $create_order = false;
    protected $update_order = false;
    protected $order_status = false;

    public function postProcess()
    {
        $cart_id = Tools::getValue('transactionid');
        $type = Tools::getValue('type');
        $this->lock_file = 'multisafepay_cart_' . $cart_id . '.lock';
        $tries = 1;
        while ($tries < $this->timeToWait) {
            if (file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . $this->lock_file)) {
                sleep(1);
                $tries++;
            } else {
                fopen(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . $this->lock_file, "w");
                $tries = 99;
            }
        }

        if ($tries == $this->timeToWait) {
            $this->unlock();
            if ($type == "notification") {
                die('ng');
            } else {
                $this->errors[] = $this->module->l('There was an error while redirecting you to the order confirmation page. You are redirected to the order history page instead. Because of this it can take some minutes before your new order will be visible within your account', 'validation');
                $this->redirectWithNotifications($this->context->link->getPageLink('history', true, null, array()));
            }
        }

        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

        $this->transaction = $multisafepay->orders->get($endpoint = 'orders', $cart_id, $body = array(), $query_string = false);

        if (Configuration::get('MULTISAFEPAY_DEBUG')) {
            $logger = new FileLogger(0);
            $logger->setFilename(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'logs/multisafepay_cart_' . $cart_id . '.log');
            $logger->logDebug("Status Request data -------------------------");
            $logger->logDebug($this->transaction);
        }

        if ($type == "redirect") {

            /* wait maximal xx seconds to give PrestaShop the time to create the order */
            $i = 0;
            do {
                sleep (1);
                $order = new Order(Order::getOrderByCartId((int) $cart_id));
            } while ( empty ($order->id) && ++$i < $this->timeToWait);

            if (isset($order->id_cart) && $order->id_cart > 0) {
                $url = "index.php?controller=order-confirmation"
                        . '&key=' . $order->secure_key
                        . '&id_cart=' . $order->id_cart
                        . '&id_module=' . Tools::getValue('id_module')
                        . '&id_order=' . $order->id;
                $this->unlock();
                Tools::redirect($url);
                exit;
            } else {
                $cart = $this->context->cart;

                if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
                    Tools::redirect('index.php?controller=order&step=1');
                }

                // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
                $authorized = false;
                foreach (Module::getPaymentModules() as $module) {
                    if ($module['name'] == 'multisafepay') {
                        $authorized = true;
                        break;
                    }
                }

                if (!$authorized) {
                    die($this->module->l('This payment method is not available.', 'validation'));
                }
                $customer = new Customer($cart->id_customer);

                if (!Validate::isLoadedObject($customer)) {
                    Tools::redirect('index.php?controller=order&step=1');
                }

                $currency = $this->context->currency;
                $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

                switch ($this->transaction->status) {
                    case 'initialized':
                        $this->create_order = false;
                        if ($this->transaction->payment_details->type == 'BANKTRANS') {
                            $this->create_order = true;
                            $this->order_status = Configuration::get('PS_OS_BANKWIRE');
                            $this->update_order = false;
                        }
                        break;
                    case 'completed':
                        $this->create_order = true;
                        $this->order_status = Configuration::get('PS_OS_PAYMENT');
                        break;
                    case 'uncleared':
                        $this->create_order = true;
                        $this->order_status = Configuration::get('MULTISAFEPAY_OS_UNCLEARED');
                        break;
                }

                if ($this->create_order) {
                    $this->module->validateOrder((int) $cart_id, $this->order_status, $total, $this->transaction->payment_details->type, null, '', (int) $currency->id, false, $customer->secure_key);
                    $order = new Order(Order::getOrderByCartId((int) $cart_id));
                    $this->unlock();
                    Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . (int) $this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key);
                    exit;
                } else {
                    $this->errors[] = $this->module->l('Your transaction was processed, but the correct transaction status couldn\'t not be determined and you are redirected to your order history page instead of the order confirmation page. Transaction status:','validation') . $this->transaction->status;
                    $this->unlock();
                    $this->redirectWithNotifications($this->context->link->getPageLink('history', true, null, array()));
                    exit;
                }
            }
        } else {
            switch ($this->transaction->status) {
                case 'initialized':
                    $this->create_order = false;
                    if ($this->transaction->payment_details->type == 'BANKTRANS') {
                        $this->create_order = true;
                        $this->order_status = Configuration::get('PS_OS_BANKWIRE');
                    }
                    break;
                case 'declined':
                    $this->create_order = false;
                    break;
                case 'cancelled':
                    $this->create_order = false;
                    break;
                case 'completed':
                    $this->create_order = true;
                    $this->order_status = Configuration::get('PS_OS_PAYMENT');
                    break;
                case 'expired':
                    $this->create_order = false;
                    break;
                case 'uncleared':
                    $this->create_order = true;
                    $this->order_status = Configuration::get('MULTISAFEPAY_OS_UNCLEARED');
                    break;
                case 'refunded':
                    $this->create_order = false;
                    $this->order_status = Configuration::get('PS_OS_REFUND');
                    break;
                case 'partial_refunded':
                    $this->order_status = Configuration::get('MULTISAFEPAY_OS_PARTIAL_REFUNDED');
                    $this->create_order = false;
                    break;
                case 'void':
                    $this->create_order = false;
                    break;
            }


            $order = new Order(Order::getOrderByCartId((int) $cart_id));
            if (isset($order->id_cart) && $order->id_cart > 0) {
                $status_history = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'order_history` WHERE `id_order` = ' . (int) $order->id);
                //Get all previous order stats, if status is not yet added to the order in the past then we update, else the update was already done and the status was changes after and updating it can give a conflict with other processing flows.
                $status_in_history = false;

                foreach ($status_history as $key => $status) {

                    if ($status['id_order_state'] == $this->order_status) {
                        //status for the update is already in the history, so we don't update
                        $status_in_history = true;
                    }
                }

                //status was not in history so lets update the order
                if (!$status_in_history) {
                    $history = new OrderHistory();
                    $history->id_order = (int) $order->id;

                    if ($order->getCurrentState() != $this->order_status) {
                        $history->changeIdOrderState((int) $this->order_status, $order->id);
                        $history->add();
                    }

                    if ($this->transaction->status == 'completed') {
                        $payments = $order->getOrderPaymentCollection();
                        $payments[0]->transaction_id = $this->transaction->transaction_id;
                        $payments[0]->update();
                    }
                }
            } else {
                if ($this->create_order) {
                    $cart = new Cart($cart_id);
                    $customer = new Customer($cart->id_customer);
                    $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
                    $this->module->validateOrder((int) $cart_id, $this->order_status, $total, $this->transaction->payment_details->type, null, '', (int) $cart->id_currency, false, $customer->secure_key);

                    $order = new Order(Order::getOrderByCartId((int) $cart_id));
                    $this->unlock();
                    Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . (int) $this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key);
                    exit;
                }
            }
        }

        $this->unlock();
        echo 'OK';
        exit;
    }

    private function unlock()
    {
        if (file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . $this->lock_file))
            unlink(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . $this->lock_file);
    }

}
