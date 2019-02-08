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
        $this->lock_file = _PS_MODULE_DIR_ . 'multisafepay' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . 'multisafepay_cart_' . $cart_id . '.lock';
        $tries = 1;
        while ($tries < $this->timeToWait) {
            if (file_exists($this->lock_file)) {
                sleep(1);
                $tries++;
            } else {
                fopen($this->lock_file, "w");
                $tries = 99;
            }
        }

        if ($tries == $this->timeToWait) {
            $this->unlock();
            if ($type == "notification") {
                die('ng');
            } else {
                $this->errors[] = $this->module->l('The verification of your payment takes more time than expected.', 'validation');
                $this->errors[] = $this->module->l('Therefore we cannot redirect you to the order confirmation page.', 'validation');
                $this->errors[] = $this->module->l('You are redirected to the order history page instead.', 'validation');
                $this->errors[] = $this->module->l('Because of this it can take some minutes before your new order will be visible within your account.', 'validation');
                $this->redirectWithNotifications($this->context->link->getPageLink('history', true, null, array()));
            }
        }

        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

        $this->transaction = $multisafepay->orders->get($endpoint = 'orders', $cart_id, $body = array(), $query_string = false);

        if (Configuration::get('MULTISAFEPAY_DEBUG')) {
            $logger = new FileLogger(0);
            $logger->setFilename(_PS_MODULE_DIR_ . 'multisafepay' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'multisafepay_cart_' . $cart_id . '.log');
            $logger->logDebug("Status Request data -------------------------");
            $logger->logDebug($this->transaction);
        }


        $cart = new Cart($cart_id);
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }


        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $paid  = (float)($this->transaction->amount / 100);


        if ($type == "redirect") {

            $order = new Order(Order::getOrderByCartId((int) $cart_id));

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
                    default:
                        $this->create_order = false;
                        $this->order_status = Configuration::get('PS_OS_ERROR');
                        break;
                }

                if ($this->create_order) {

                    if ($paid != $total ){
                        $this->order_status = Configuration::get('PS_OS_ERROR');
                    }

                    $extra_properties = array('transaction_id' => $this->transaction->transaction_id);
                    $this->module->validateOrder((int) $cart_id, $this->order_status, $paid, $this->transaction->payment_details->type, null, $extra_properties, (int) $cart->id_currency, false, $customer->secure_key);
                    $order = new Order(Order::getOrderByCartId((int) $cart_id));
                    if ($paid != $total){
                         $this->addMessage($order, $customer);
                    }

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
                    $this->order_status = Configuration::get('PS_OS_CANCELED');
                    break;
                case 'cancelled':
                    $this->create_order = false;
                    $this->order_status = Configuration::get('PS_OS_CANCELED');
                    break;
                case 'completed':
                    $this->create_order = true;
                    $this->order_status = Configuration::get('PS_OS_PAYMENT');
                    break;
                case 'expired':
                    $this->create_order = false;
                    $this->order_status = Configuration::get('PS_OS_CANCELED');
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
                    $this->order_status = Configuration::get('PS_OS_CANCELED');
                    break;

                case 'chargedback':
                    $this->create_order = false;
                    $this->order_status = Configuration::get('MULTISAFEPAY_OS_CHARGEBACK');
                    break;

                case 'shipped':
                    $this->create_order = false;
                    $this->order_status = Configuration::get('PS_OS_SHIPPING');
                    break;

                default:
                    $this->create_order = false;
                    $this->order_status = Configuration::get('PS_OS_ERROR');
                    break;
            }


            $order = new Order(Order::getOrderByCartId((int) $cart_id));
            if (isset($order->id_cart) && $order->id_cart > 0) {
                if ($paid == $total){

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
                }
            } else {
                if ($this->create_order) {

                    if ($paid != $total ){
                        $this->order_status = Configuration::get('PS_OS_ERROR');
                    }

                    $extra_properties = array('transaction_id' => $this->transaction->transaction_id);
                    $this->module->validateOrder((int) $cart_id, $this->order_status, $paid, $this->transaction->payment_details->type, null, $extra_properties, (int) $cart->id_currency, false, $customer->secure_key);
                    $order = new Order(Order::getOrderByCartId((int) $cart_id));
                    if ($paid != $total){
                         $this->addMessage($order, $customer);
                    }

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


    private function addMessage($order, $customer)
    {
        $message = $this->module->l('A payment error occurred.', 'validation') . "\r\n" .
                   $this->module->l('The order amount differs from the amount paid.', 'validation') . "\r\n" .
                   $this->module->l('This often happens when the payment is done through a second chance mail and the shopping cart has changed after the first payment attempt.', 'validation') . "\r\n" .
                   $this->module->l('Payment has been made for the following item(s):', 'validation') . "\r\n";

        foreach ($this->transaction->shopping_cart->items as $item => $product){
            $message .= sprintf("%d x %s \r\n", $product->quantity, $product->name);
        }

        $message = strip_tags($message);
        if (Validate::isCleanHtml($message)) {

            // Add this message in the customer thread
            $customer_thread = new CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = (int)$order->id_customer;
            $customer_thread->id_shop = (int)$order->id_shop;
            $customer_thread->id_order = (int)$order->id;
            $customer_thread->id_lang = (int)$order->id_lang;
            $customer_thread->email = $customer->email;
            $customer_thread->status = 'open';
            $customer_thread->token = Tools::passwdGen(12);
            $customer_thread->add();

            $customer_message = new CustomerMessage();
            $customer_message->id_customer_thread = $customer_thread->id;
            $customer_message->id_employee = 0;
            $customer_message->message = $message;
            $customer_message->private = 1;
            $customer_message->add();
        }
    }


    private function unlock()
    {
        if (file_exists($this->lock_file))
            unlink($this->lock_file);
    }
}
