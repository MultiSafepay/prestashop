<?php
/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
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

class MultisafepayValidationModuleFrontController extends ModuleFrontController
{

    protected $transaction;
    protected $orderStatus = array();
    protected $order_status = false;
    protected $total;
    protected $paid;

    public function postProcess()
    {
        $cart_id = Tools::getValue('transactionid');
        $type = Tools::getValue('type');

        // Sometimes the redirect- and notification url are triggered at the exact same time, causing double orders.
        // Now hold redirect for 1 sec. to give notification the change to execute first.
        if ($type == "redirect") {
            sleep(1);
        }

        // if order exists but the payment is not processed by MultiSafepay
        $order = new Order(Order::getIdByCartId((int)$cart_id));
        if ($order->module && $order->module != 'multisafepay') {
            echo 'ok';
            exit;
        }

        $cart = new Cart($cart_id);
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $multisafepay = new MspClient();
        $multisafepay->initialize(
            Configuration::get('MULTISAFEPAY_ENVIRONMENT'),
            Configuration::get('MULTISAFEPAY_API_KEY')
        );

        $this->transaction = $multisafepay->orders->get($endpoint = 'orders', $cart_id, $body = array(), $query_string = false);

        if (Configuration::get('MULTISAFEPAY_DEBUG')) {
            $logger = new FileLogger(0);
            $logger->setFilename(_PS_MODULE_DIR_ . 'multisafepay' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'multisafepay_cart_' . $cart_id . '.log');
            $logger->logDebug("Transaction-data -------------------------");
            $logger->logDebug($this->transaction);
        }


        $this->total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $this->paid = (float)($this->transaction->amount / 100);

        $this->order_status = $this->setStatus();

        if (isset($order->id_cart) && $order->id_cart > 0) {
            $this->updateOrderStatus($order);
        } else {
            $this->createOrder($cart, $customer);
        }

        if (Configuration::get('MULTISAFEPAY_ENABLE_TOKEN')) {
            $this->updateTokenization();
        }
        echo 'OK';
        exit;
    }

    /**
     * @param $cart
     * @param $customer
     */
    private function createOrder($cart, $customer)
    {
        if (!$this->allowOrderCreation()) {
            return;
        }

        if ($this->paid !== $this->total) {
            $this->order_status = Configuration::get('PS_OS_ERROR');
        }

        $helper = new Helper;
        $used_payment_method = $helper->getPaymentMethod($this->transaction->payment_details->type);
        $extra_properties = array('transaction_id' => $this->transaction->transaction_id);

        $this->module->validateOrder((int)$cart->id, $this->order_status, $this->paid, $used_payment_method, null, $extra_properties, (int)$cart->id_currency, false, $customer->secure_key);
        $order = new Order(Order::getIdByCartId((int)$cart->id));
        if ($this->paid !== $this->total) {
            $this->addMessage($order, $customer);
        }
    }

    /**
     * @param $order
     */
    private function updateOrderStatus($order)
    {
        if ($this->paid !== $this->total) {
            return;
        }

        // Do not update the orderstatus if the new order status is already been added in the past
        $statusHistory = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'order_history` WHERE `id_order` = ' . (int)$order->id);
        $statusUpdateAllowed = true;

        foreach ($statusHistory as $key => $status) {
            if ($status['id_order_state'] === $this->order_status) {
                $statusUpdateAllowed = false;
                break;
            }
        }

        if ($statusUpdateAllowed) {
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;

            if ($order->getCurrentState() !== $this->order_status) {
                $history->changeIdOrderState((int)$this->order_status, $order->id);
                $history->add();
            }

            if ($this->transaction->status == 'completed') {
                $payments = $order->getOrderPaymentCollection();
                $payments[0]->transaction_id = $this->transaction->transaction_id;
                $payments[0]->update();
            }
        }
    }

    private function addMessage($order, $customer)
    {
        $message = $this->module->l('A payment error occurred.', 'validation') . "\r\n" .
            $this->module->l('The order amount differs from the amount paid.', 'validation') . "\r\n" .
            $this->module->l('This often happens when the payment is done through a second chance mail and the shopping cart has changed after the first payment attempt.', 'validation') . "\r\n" .
            $this->module->l('Payment has been made for the following item(s):', 'validation') . "\r\n";

        foreach ($this->transaction->shopping_cart->items as $item => $product) {
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


    private function updateTokenization()
    {
        if (isset($this->transaction->payment_details->type)
            && isset($this->transaction->payment_details->last4)
            && isset($this->transaction->payment_details->card_expiry_date)) {
            $phpEncryption = new PhpEncryption(_NEW_COOKIE_KEY_);

            Db::getInstance()->update(
                'multisafepay_tokenization',
                array(
                    'recurring_id' => $phpEncryption->encrypt($this->transaction->payment_details->recurring_id),
                    'cc_type' => $this->transaction->payment_details->type,
                    'cc_last4' => $this->transaction->payment_details->last4,
                    'cc_expiry_date' => $this->transaction->payment_details->card_expiry_date,
                ),
                'order_id = ' . pSQL(Tools::getValue('transactionid'))
            );
        }
    }

    private function setStatus()
    {
        $orderStatus = array(
            'initialized' => Configuration::get('MULTISAFEPAY_OS_AWAITING_BANK_TRANSFER_PAYMENT'),
            'declined' => Configuration::get('PS_OS_CANCELED'),
            'cancelled' => Configuration::get('PS_OS_CANCELED'),
            'completed' => Configuration::get('PS_OS_PAYMENT'),
            'expired' => Configuration::get('PS_OS_CANCELED'),
            'uncleared' => Configuration::get('MULTISAFEPAY_OS_UNCLEARED'),
            'refunded' => Configuration::get('PS_OS_REFUND'),
            'partial_refunded' => Configuration::get('MULTISAFEPAY_OS_PARTIAL_REFUNDED'),
            'void' => Configuration::get('PS_OS_CANCELED'),
            'chargedback' => Configuration::get('MULTISAFEPAY_OS_CHARGEBACK'),
            'shipped' => Configuration::get('PS_OS_SHIPPING')
        );
        return isset($orderStatus[$this->transaction->status]) ? $orderStatus[$this->transaction->status] : Configuration::get('PS_OS_ERROR');
    }

    private function allowOrderCreation()
    {
        $allowOrderCreation = false;

        switch ($this->transaction->status) {
            case 'initialized':
                if ($this->transaction->payment_details->type === 'BANKTRANS') {
                    $allowOrderCreation = true;
                }
                break;
            case 'completed':
            case 'uncleared':
                $allowOrderCreation = true;
                break;
        }
        return $allowOrderCreation;
    }
}
