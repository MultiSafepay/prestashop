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

require(dirname(__FILE__) . '/models/Api/MspClient.php');
require(dirname(__FILE__) . '/helpers/CheckConnection.php');


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Multisafepay extends PaymentModule
{

    protected $_postErrors = array();
    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;
    public $countries;
    public $currencies;
    public $carriers;
    public $groups;

    public $multisafepay_js;
    public $multisafepay_css;
    /*
     * This array contains all supported gifcards and is used to generate the configuration an paymentOptions
     */
    public $giftcards = array(
        array("code" => "webshopgiftcard", "name" => "Webshopgiftcard", 'config' => true),
        array("code" => "babygiftcard", "name" => "Babygiftcard", 'config' => true),
        array("code" => "boekenbon", "name" => "Boekenbon", 'config' => true),
        array("code" => "erotiekbon", "name" => "Erotiekbon", 'config' => true),
        array("code" => "parfumcadeaukaart", "name" => "Parfumcadeaukaart", 'config' => true),
        array("code" => "yourgift", "name" => "Yourgift", 'config' => true),
        array("code" => "wijncadeau", "name" => "Wijncadeau", 'config' => true),
        array("code" => "gezondheidsbon", "name" => "Gezonheidsbon", 'config' => true),
        array("code" => "fashioncheque", "name" => "Fashioncheque", 'config' => true),
        array("code" => "fashiongiftcard", "name" => "Fashiongiftcard", 'config' => true),
        array("code" => "podium", "name" => "Podium", 'config' => true),
        array("code" => "vvvbon", "name" => "VVV Bon", 'config' => true),
        array("code" => "sportenfit", "name" => "Sport en Fit", 'config' => true),
        array("code" => "goodcard", "name" => "Goodcard", 'config' => true),
        array("code" => "nationaletuinbon", "name" => "Nationale tuinbon", 'config' => true),
        array("code" => "nationaleverwencadeaubon", "name" => "Nationale verwencadeaubon", 'config' => true),
        array("code" => "beautyandwellness", "name" => "Beauty and wellness", 'config' => true),
        array("code" => "fietsenbon", "name" => "Fietsenbon", 'config' => true),
        array("code" => "wellnessgiftcard", "name" => "Wellnessgiftcard", 'config' => true),
        array("code" => "winkelcheque", "name" => "Winkelcheque", 'config' => true),
        array("code" => "givacard", "name" => "Givacard", 'config' => true)
    );

    /*
     * This array contains all supported paymentmethods and is used to generate the configuration an paymentOptions
     */
    public $gateways = array(
        array("code" => "ideal", "name" => "Ideal", 'config' => true),
        array("code" => "dotpay", "name" => "Dotpay", 'config' => true),
        array("code" => "payafter", "name" => "Betaal na Ontvangst", 'config' => true),
        array("code" => "einvoice", "name" => "E-invoice", 'config' => true),
        array("code" => "klarna", "name" => "Klarna Invoice", 'config' => true),
        array("code" => "mistercash", "name" => "Bancontact", 'config' => true),
        array("code" => "visa", "name" => "Visa", 'config' => true),
        array("code" => "eps", "name" => "Eps", 'config' => true),
        array("code" => "mastercard", "name" => "Mastercard", 'config' => true),
        array("code" => "banktrans", "name" => "Banktransfer", 'config' => true),
        array("code" => "psafecard", "name" => "Paysafecard", 'config' => true),
        array("code" => "maestro", "name" => "Maestro", 'config' => true),
        array("code" => "paypal", "name" => "PayPal", 'config' => true),
        array("code" => "giropay", "name" => "Giropay", 'config' => true),
        array("code" => "directbank", "name" => "Sofort", 'config' => true),
        array("code" => "inghome", "name" => "ING Homepay", 'config' => true),
        array("code" => "belfius", "name" => "Belfius", 'config' => true),
        array("code" => "trustpay", "name" => "TrustPay", 'config' => true),
        array("code" => "kbc", "name" => "KBC", 'config' => true),
        array("code" => "dirdeb", "name" => "Direct Debit", 'config' => true),
        array("code" => "alipay", "name" => "AliPay", 'config' => true),
        array("code" => "connect", "name" => "MultiSafepay", 'config' => true),
        array("code" => "amex", "name" => "American Express", 'config' => true)
    );

    public function __construct()
    {
        $this->name = 'multisafepay';
        $this->tab = 'payments_gateways';
        $this->version = '4.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'MultiSafepay';
        $this->controllers = array('validation', 'payment');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('MultiSafepay');
        $this->description = $this->l('Process payments by using MultiSafepay secure payment processing');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }

        /*
         * Sort the gateways based by provided sort order configuration value
         */
        for ($i = 0; $i < count($this->gateways); $i ++) {
            $this->gateways[$i]['sort'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $this->gateways[$i]['code'] . '_SORT');
        }

        usort($this->gateways, function($a, $b) {
            return $a['sort'] - $b['sort'];
        });

        for ($i = 0; $i < count($this->gateways); $i ++) {
            if (empty($this->gateways[$i]['sort'])) {
                $temp_array = $this->gateways[$i];
                unset($this->gateways[$i]);
                $this->gateways[] = $temp_array;
            }
        }

        /*
            Define the location of the CSS and JS file
        */
        $protocol = Tools::getShopDomainSsl(true, true);
        $this->multisafepay_js  = $protocol . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/multisafepay.js';
        $this->multisafepay_css = $protocol . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/multisafepay.css';

        /*
         * Sort the giftcards based by provided sort order configuration value
         */
        for ($i = 0; $i < count($this->giftcards); $i ++) {
            $this->giftcards[$i]['sort'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $this->giftcards[$i]['code'] . '_SORT');
        }

        usort($this->giftcards, function($a, $b) {
            return $a['sort'] - $b['sort'];
        });

        for ($i = 0; $i < count($this->giftcards); $i ++) {
            if (empty($this->giftcards[$i]['sort'])) {
                $temp_array = $this->giftcards[$i];
                unset($this->giftcards[$i]);
                $this->giftcards[] = $temp_array;
            }
        }
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn') || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('actionFrontControllerSetMedia')
        ) {
            return false;
        }

        $multisafepay_stats = array(
            'uncleared' => array(
                'name' => 'uncleared',
                'send_mail' => false,
                'color' => '#ec2e15',
                'invoice' => false,
                'template' => '',
                'paid' => false,
                'logable' => false
            ),
            'partial_refunded' => array(
                'name' => 'partial_refunded',
                'send_mail' => true,
                'color' => '#ec2e15',
                'invoice' => false,
                'template' => 'refund',
                'paid' => false,
                'logable' => false
            ),
        );

        foreach ($multisafepay_stats as $status => $value) {
            if (!Configuration::get('MULTISAFEPAY_OS_' . Tools::strtoupper($status))) {
                $order_state = new OrderState();
                $order_state->name = array();
                foreach (Language::getLanguages() as $language) {
                    $order_state->name[$language['id_lang']] = 'MultiSafepay ' . $value['name'];
                }

                $order_state->send_email = $value['send_mail'];
                $order_state->color = $value['color'];
                $order_state->hidden = false;
                $order_state->delivery = false;
                $order_state->logable = $value['logable'];
                $order_state->invoice = $value['invoice'];
                $order_state->template = $value['template'];
                $order_state->paid = $value['paid'];
                $order_state->add();
                Configuration::updateValue('MULTISAFEPAY_OS_' . Tools::strtoupper($status), (int) $order_state->id);
            }
        }
        $this->initializeConfig();
        return true;
    }


    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path . 'views/js/multisafepay_front.js');
    }



    protected function initializeConfig()
    {
        $default_currency = $this->context->currency->id;
        $default_country = $this->context->country->id;
        $this->groups = Group::getGroups($this->context->language->id);
        $this->carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);

        foreach ($this->giftcards as $giftcard) {
            Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $default_currency, 'on');
            Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $default_country, 'on');

            foreach ($this->groups as $group) {
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group['id_group'], 'on');
            }

            foreach ($this->carriers as $carrier) {
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_carrier'], 'on');
            }
        }
        foreach ($this->gateways as $gateway) {
            Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $default_currency, 'on');
            Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $default_country, 'on');

            foreach ($this->groups as $group) {
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group'], 'on');
            }

            foreach ($this->carriers as $carrier) {
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_carrier'], 'on');
            }
        }
    }

    public function uninstall()
    {
        //@TODO: Also add Configuration::deleteByName for each gateway/giftcard + restrictions

        $this->unregisterHook('paymentOptions');
        $this->unregisterHook('paymentReturn');
        $this->unregisterHook('actionOrderStatusPostUpdate');

        Configuration::deleteByName('MULTISAFEPAY_API_KEY');
        Configuration::deleteByName('MULTISAFEPAY_DEBUG');
        Configuration::deleteByName('MULTISAFEPAY_ENVIRONMENT');
        Configuration::deleteByName('MULTISAFEPAY_TIME_ACTIVE');
        Configuration::deleteByName('MULTISAFEPAY_TIME_UNIT');

        return parent::uninstall();
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if ($params['newOrderStatus']->id == Configuration::get('PS_OS_SHIPPING')) {
            $order = new Order(Order::getOrderByCartId($params['cart']->id));
            if ($order->payment == 'KLARNA' || $order->payment == 'PAYAFTER' || $order->payment == 'EINVOICE') {
                $carrier = new Carrier((int) $params['cart']->id_carrier);

                $multisafepay = new MspClient();
                $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
                $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

                $endpoint = 'orders/' . $params['cart']->id;
                $ship_data = array(
                    "tracktrace_code" => '',
                    "carrier" => $carrier->name,
                    "ship_date" => date('Y-m-d H:i:s'),
                    "reason" => 'Shipped'
                );
                $result = $multisafepay->orders->patch($ship_data, $endpoint);

                if (!empty($result->success)) {
                    $this->transaction = $multisafepay->orders->get($endpoint = 'orders', $params['cart']->id, $body = array(), $query_string = false);
                    if ($this->transaction->payment_details->type == 'KLARNA') {
                        $msg = new Message();
                        $msg->message = $this->l('https://online.klarna.com/invoices/' . $this->transaction->payment_details->external_transaction_id . '.pdf');
                        $msg->id_order = $params['cart']->id;
                        $msg->private = True;
                        $msg->save();
                    }
                }
            }
        }
    }

    /*
     * _postProcess saves the data of the specific form. Each tab has its own form so for each tab a submit is defined.
     */

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('MULTISAFEPAY_API_KEY', Tools::getValue('MULTISAFEPAY_API_KEY'));
            Configuration::updateValue('MULTISAFEPAY_DEBUG', Tools::getValue('MULTISAFEPAY_DEBUG'));
            Configuration::updateValue('MULTISAFEPAY_ENVIRONMENT', Tools::getValue('MULTISAFEPAY_ENVIRONMENT'));
            Configuration::updateValue('MULTISAFEPAY_TIME_ACTIVE', Tools::getValue('MULTISAFEPAY_TIME_ACTIVE'));
            Configuration::updateValue('MULTISAFEPAY_TIME_UNIT', Tools::getValue('MULTISAFEPAY_TIME_UNIT'));
            $this->context->smarty->assign('configuration_settings_saved', $this->l('Settings updated'));
            return;
        }

        if (Tools::isSubmit('btnGatewaysSubmit')) {
            foreach ($this->gateways as $gateway) {
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"], Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"]));
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE', Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE'));
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_SORT', Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_SORT'));
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MIN_AMOUNT', Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MIN_AMOUNT'));
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MAX_AMOUNT', Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MAX_AMOUNT'));
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_DESC', Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_DESC'));
            }
            $this->context->smarty->assign('gateway_settings_saved', $this->l('Gateway settings updated'));
            return;
        }

        if (Tools::isSubmit('btnGiftcardsSubmit')) {
            foreach ($this->giftcards as $giftcard) {
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"], Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"]));
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_TITLE', Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_TITLE'));
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_SORT', Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_SORT'));
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_DESC', Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_DESC'));
            }
            $this->context->smarty->assign('giftcard_settings_saved', $this->l('Giftcard settings updated'));
            return;
        }

        if (Tools::isSubmit('btnSubmitGiftcardConfig')) {
            foreach ($this->giftcards as $giftcard) {
                foreach ($this->currencies as $currency) {
                    Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $currency['id_currency'], Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $currency['id_currency']));
                }
                foreach ($this->groups as $group) {
                    Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group['id_group'], Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group['id_group']));
                }
                foreach ($this->carriers as $carrier) {
                    Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_carrier'], Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_carrier']));
                }
                foreach ($this->countries as $country) {
                    Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $country['id_country'], Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $country['id_country']));
                }
            }
            $this->context->smarty->assign('giftcard_settings_saved', $this->l('Giftcard restrictions updated'));
            return;
        }

        if (Tools::isSubmit('btnSubmitGatewayConfig')) {
            foreach ($this->gateways as $gateway) {
                foreach ($this->currencies as $currency) {
                    Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $currency['id_currency'], Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $currency['id_currency']));
                }
                foreach ($this->groups as $group) {
                    Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group'], Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group']));
                }
                foreach ($this->carriers as $carrier) {
                    Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_carrier'], Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_carrier']));
                }
                foreach ($this->countries as $country) {
                    Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $country['id_country'], Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $country['id_country']));
                }
            }
            $this->context->smarty->assign('gateway_restrictions_saved', $this->l('Gateway restrictions updated'));
            return;
        }
    }

    /*
     * getContents creates the content for the module. getMultiSafepayTabs creates the content for the specific tabs and are used for the main tabs content view
     */

    private function _postValidation()
    {
        $postMessages['errors'] = array();
        $postMessages['warnings'] = array();


        if ( Tools::isSubmit('btnSubmit') &&
            ( Configuration::get('MULTISAFEPAY_ENVIRONMENT') != Tools::getValue('MULTISAFEPAY_ENVIRONMENT') ||
              Configuration::get('MULTISAFEPAY_API_KEY')     != Tools::getValue('MULTISAFEPAY_API_KEY') ) ) {

            $postMessages['errors'] = $this->checkApiKey();
            return $postMessages;
        }

        if (Tools::isSubmit('btnGatewaysSubmit')) {
            $postMessages['warnings'] = $this->getActiveGateways();
            return $postMessages;
        }

         if (Tools::isSubmit('btnGiftcardsSubmit')) {
            $postMessages['warnings'] = $this->getActiveGiftcards();
            return $postMessages;
        }
    }

    public function getContent()
    {
        $this->currencies = Currency::getCurrencies();
        $this->groups     = Group::getGroups($this->context->language->id);
        $this->carriers   = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $this->countries  = Country::getCountries($this->context->language->id);

        $protocol = Tools::getShopDomainSsl(true, true);
        $multisafepay_js  = $protocol . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/multisafepay.js';
        $multisafepay_css = $protocol . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/multisafepay.css';

        if (!Tools::getValue('tab')) {
            $active_tab = 'main_configuration';
        } else {
            $active_tab = Tools::getValue('multisafepay_tab');
        }

        $postMessages = $this->_postValidation();

        if (empty($postMessages['errors'])) {
            $this->_postProcess();
        }

        $this->context->smarty->assign(array(
            'tabs'              => $this->getMultiSafepayTabs(),
            'active_tab'        => $active_tab,
            'multisafepay_js'   => $multisafepay_js,
            'multisafepay_css'  => $multisafepay_css,
            'errors'            => $postMessages['errors'],
            'warnings'          => $postMessages['warnings']
        ));


        return $this->display(__FILE__, 'views/templates/admin/tabs.tpl');
    }

    /*
     * getMultiSafepayTabs creates the content for the specific tabs.
     */

    protected function getMultiSafepayTabs()
    {
        $tabs = array();
        $tabs[] = array(
            'id' => 'main_configuration',
            'title' => 'MultiSafepay configuration',
            'content' => $this->getMainConfiguration()
        );

        $tabs[] = array(
            'id' => 'gateway_configuration',
            'title' => 'Payment Methods',
            'content' => $this->getGateways()
        );

        $tabs[] = array(
            'id' => 'giftcard_configuration',
            'title' => 'Giftcards',
            'content' => $this->getGiftcards()
        );

        $tabs[] = array(
            'id' => 'gateway_restrictions_configuration',
            'title' => 'Payment Restrictions',
            'content' => $this->getGatewayRestrictions()
        );

        $tabs[] = array(
            'id' => 'giftcard_restrictions_configuration',
            'title' => 'Giftcard Restrictions',
            'content' => $this->getGiftcardRestrictions()
        );



        return $tabs;
    }

    /*
     * getGatewayRestrictionssets all data used within the restrictions page.
     */

    protected function getGatewayRestrictions()
    {
        foreach ($this->gateways as $key => $gateway) {
            $this->gateways[$key]['active'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"]);

            foreach ($this->currencies as $currency) {
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $currency['id_currency']) == 'on') {
                    $this->gateways[$key]['currency'][$currency['id_currency']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $currency['id_currency']);
                }
            }

            foreach ($this->groups as $group) {
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group']) == "on") {
                    $this->gateways[$key]['group'][$group['id_group']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group']);
                }
            }

            foreach ($this->carriers as $carrier) {
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_carrier']) == 'on') {
                    $this->gateways[$key]['carrier'][$carrier['id_carrier']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_carrier']);
                }
            }

            foreach ($this->countries as $country) {
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $country['id_country']) == 'on') {
                    $this->gateways[$key]['country'][$country['id_country']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $country['id_country']);
                }
            }
        }

        //print_r($this->gateways);exit;


        $this->context->smarty->assign(array(
            'groups' => $this->groups,
            'currencies' => $this->currencies,
            'countries' => $this->countries,
            'carriers' => $this->carriers,
            'gateways' => $this->gateways,
            'currency' => $this->l('Currency'),
            'currency_restriction' => $this->l('Currency Restrictions'),
            'group' => $this->l('Group'),
            'group_restriction' => $this->l('Group Restrictions'),
            'carrier' => $this->l('Carrier'),
            'carrier_restriction' => $this->l('Carrier Restrictions'),
            'country' => $this->l('Country'),
            'country_restriction' => $this->l('Country Restrictions'),
            'gateways_restriction' => $this->l('Gateways'),
            'giftcards_restriction' => $this->l('Giftcards'),
            'save' => $this->l('Save')
        ));

        return $this->display(__FILE__, 'views/templates/admin/gateway_restrictions.tpl');
    }

    /*
     * getGiftcardRestrictions all data used within the restrictions page.
     */

    protected function getGiftcardRestrictions()
    {
        foreach ($this->giftcards as $key => $giftcard) {
            $this->giftcards[$key]['active'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"]);

            foreach ($this->currencies as $currency) {
                if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $currency['id_currency']) == 'on') {
                    $this->giftcards[$key]['currency'][$currency['id_currency']] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $currency['id_currency']);
                }
            }

            foreach ($this->groups as $group) {
                if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group['id_group']) == 'on') {
                    $this->giftcards[$key]['group'][$group['id_group']] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group['id_group']);
                }
            }

            foreach ($this->carriers as $carrier) {
                if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_carrier']) == 'on') {
                    $this->giftcards[$key]['carrier'][$carrier['id_carrier']] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_carrier']);
                }
            }

            foreach ($this->countries as $country) {
                if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $country['id_country']) == 'on') {
                    $this->giftcards[$key]['country'][$country['id_country']] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $country['id_country']);
                }
            }
        }



        $this->context->smarty->assign(array(
            'groups' => $this->groups,
            'currencies' => $this->currencies,
            'countries' => $this->countries,
            'carriers' => $this->carriers,
            'giftcards' => $this->giftcards,
            'currency' => $this->l('Currency'),
            'currency_restriction' => $this->l('Currency Restrictions'),
            'group' => $this->l('Group'),
            'group_restriction' => $this->l('Group Restrictions'),
            'carrier' => $this->l('Carrier'),
            'carrier_restriction' => $this->l('Carrier Restrictions'),
            'country' => $this->l('Country'),
            'country_restriction' => $this->l('Country Restrictions'),
            'gateways_restriction' => $this->l('Gateways'),
            'giftcards_restriction' => $this->l('Giftcards'),
            'save' => $this->l('Save')
        ));

        return $this->display(__FILE__, 'views/templates/admin/giftcard_restrictions.tpl');
    }

    /*
     * getMainConfiguration all data used within the multisafepay configuration page.
     */

    protected function getMainConfiguration()
    {
        $field_values = array(
            'MULTISAFEPAY_API_KEY' => Tools::getValue('MULTISAFEPAY_API_KEY', Configuration::get('MULTISAFEPAY_API_KEY')),
            'MULTISAFEPAY_DEBUG' => Tools::getValue('MULTISAFEPAY_DEBUG', Configuration::get('MULTISAFEPAY_DEBUG')),
            'MULTISAFEPAY_ENVIRONMENT' => Tools::getValue('MULTISAFEPAY_ENVIRONMENT', Configuration::get('MULTISAFEPAY_ENVIRONMENT')),
            'MULTISAFEPAY_TIME_ACTIVE' => Tools::getValue('MULTISAFEPAY_TIME_ACTIVE', Configuration::get('MULTISAFEPAY_TIME_ACTIVE')),
            'MULTISAFEPAY_TIME_UNIT' => Tools::getValue('MULTISAFEPAY_TIME_UNIT', Configuration::get('MULTISAFEPAY_TIME_UNIT'))
        );
        $field_values['multisafepay_tab'] = 'main_configuration';

        $fields_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('MultiSafepay configuration'),
                    'icon' => 'icon-cog'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Process live transactions'),
                        'hint' => $this->trans('If enabled the LIVE API will be used, else the MultiSafepay test environment is active', array(), 'Modules.Multisafepay.Admin'),
                        'name' => 'MULTISAFEPAY_ENVIRONMENT',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                "code" => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Live')
                            ),
                            array(
                                "code" => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Test')
                            )
                        )
                    ), array(
                        'type' => 'text',
                        'label' => $this->l('MultiSafepay API key'),
                        'hint' => $this->trans('The MultiSafepay API key can be found within your Multisafepay website configuration using MultiSafepay Control', array(), 'Modules.Multisafepay.Admin'),
                        'name' => 'MULTISAFEPAY_API_KEY',
                        'required' => true
                    ),

                    array(
                        'type' => 'text',
                        'label'=>  $this->l('Time before a transaction will expire'),
                        'hint' => $this->trans('The transaction will expire after the given time.', array(), 'Modules.Multisafepay.Admin'),
                        'name' => 'MULTISAFEPAY_TIME_ACTIVE',
                        'required' => true
                    ),
                    array(
                        'type' =>  'select',
                        'name' =>  'MULTISAFEPAY_TIME_UNIT',
                        'required' =>  true,
                        'options'  =>  array(
                                          'query' => array(   array ( 'id'   => 'days',   'name' => $this->l('Days')),
                                                              array ( 'id'   => 'hours',  'name' => $this->l('Hours')),
                                                              array ( 'id'   => 'seconds','name' => $this->l('Seconds'))
                                                    ),
                                            'id'    => 'id',
                                            'name'  => 'name'
                                            )
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'multisafepay_tab'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Debug'),
                        'hint' => $this->trans('When enabled, all API requests and responses are logged within the modules/multisafepay/logs/ folder', array(), 'Modules.Multisafepay.Admin'),
                        'name' => 'MULTISAFEPAY_DEBUG',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                "code" => 'active_on',
                                'value' => 1,
                                'label' => $this->l('On')
                            ),
                            array(
                                "code" => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Off')
                            )
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $field_values,
        );
        $settings_saved = $this->display(__FILE__, 'views/templates/admin/settings_saved.tpl');
        $config_form = $helper->generateForm($fields_form);
        return $settings_saved . $config_form;
    }

    /*
     * getGateways all data used within the gateways configuration page.
     */

    protected function getGateways()
    {
        foreach ($this->gateways as $key => $gateway) {
            $this->gateways[$key]['active'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"]);
            $this->gateways[$key]['title'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE');
            $this->gateways[$key]['sort'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_SORT');
            $this->gateways[$key]['min_amount'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MIN_AMOUNT');
            $this->gateways[$key]['max_amount'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MAX_AMOUNT');
            $this->gateways[$key]['desc'] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_DESC');
        }

        $lang_iso = $this->context->language->iso_code;
        $locale = Language::getLocaleByIso($lang_iso);
        $real_locale = str_replace('-', '_', $locale);

        $supported_languages = array(
            'nl_NL',
            'en_GB',
            'it_IT',
            'de_DE',
            'fr_FR',
            'es_ES'
        );

        if (in_array($real_locale, $supported_languages)) {
            $locale = $real_locale;
        } else {
            $locale = "en_GB";
        }

        $template_vars = array(
            'gateways' => $this->gateways,
            'save' => $this->l('Save'),
            'enable' => $this->l('On'),
            'disable' => $this->l('Off'),
            'title' => $this->l('Title'),
            'sort_order' => $this->l('Sort Order'),
            'min_order_amount' => $this->l('Minimum order amount'),
            'max_order_amount' => $this->l('Maximum order amount'),
            'description' => $this->l('Frontend description'),
            'configuration' => $this->l('configuration'),
            'locale' => $locale,
            'path' => $this->_path,
        );
        $this->context->smarty->assign($template_vars);
        return $this->display(__FILE__, 'views/templates/admin/gateway_configuration.tpl');
    }

    /*
     * getGiftcards all data used within the giftcards configuration page.
     */

    protected function getGiftcards()
    {
        foreach ($this->giftcards as $key => $giftcard) {
            $this->giftcards[$key]['active'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"]);
            $this->giftcards[$key]['active'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"]);
            $this->giftcards[$key]['title'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_TITLE');
            $this->giftcards[$key]['sort'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_SORT');
            $this->giftcards[$key]['desc'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_DESC');
        }

        $lang_iso = $this->context->language->iso_code;
        $locale = Language::getLocaleByIso($lang_iso);
        $real_locale = str_replace('-', '_', $locale);

        $supported_languages = array(
            'nl_NL',
            'en_GB',
            'it_IT',
            'de_DE',
            'fr_FR',
            'es_ES'
        );

        if (in_array($real_locale, $supported_languages)) {
            $locale = $real_locale;
        } else {
            $locale = "en_GB";
        }

        $template_vars = array(
            'giftcards' => $this->giftcards,
            'save' => $this->l('Save'),
            'enable' => $this->l('On'),
            'disable' => $this->l('Off'),
            'description' => $this->l('Frontend description'),
            'configuration' => $this->l('configuration'),
            'locale' => $locale,
            'path' => $this->_path,
        );
        $this->context->smarty->assign($template_vars);
        return $this->display(__FILE__, 'views/templates/admin/giftcard_configuration.tpl');
    }

    /*
     * This hook provides the payment options. We go throug both the gateways as the giftcard and add them as an available option.
     */

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }




        $payment_options = array();

        // loop through the available MultiSafepay gateways
        foreach ($this->gateways as $gateway) {
            if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway['code']) == 1) {


                $active = false;

                /*
                 *  start restrictions
                 */
                $billing        = new Address($this->context->cart->id_address_invoice);
                $id_country     = $billing->id_country;
                $id_currency    = $params['cart']->id_currency;
                $id_carrier     = $params['cart']->id_carrier;
                $id_shop_group  = $params['cart']->id_shop_group;
                $amount         = $params['cart']->getOrderTotal(true, Cart::BOTH);


                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $id_currency) == 'on' && Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $id_shop_group) == "on" && Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $id_carrier) == 'on' && Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $id_country) == 'on') {
                    $active = true;
                }


                $min_amount = floatval (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MIN_AMOUNT'));
                $max_amount = floatval (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MAX_AMOUNT'));

                if ( (!empty($min_amount) && $amount < $min_amount) || (!empty($max_amount) && $amount > $max_amount)) {
                    $active = false;
                }

                /*
                 *  end restrictions
                 */




                if ($active) {
                    if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE')) {
                        $title = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE');
                    } else {
                        $title = $gateway['name'];
                    }

                    $lang_iso = Language::getIsoById($this->context->cart->id_lang);
                    $locale = Language::getLocaleByIso($lang_iso);
                    $real_locale = str_replace('-', '_', $locale);

                    $supported_languages = array(
                        'nl_NL',
                        'en_GB',
                        'it_IT',
                        'de_DE',
                        'fr_FR',
                        'es_ES'
                    );

                    if (in_array($real_locale, $supported_languages)) {
                        $locale = $real_locale;
                    } else {
                        $locale = "en_GB";
                    }

                    //$newOption = new PaymentOption();
                    $externalOption = new PaymentOption();
                    $externalOption->setCallToActionText($this->l($title));
                    $externalOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array('gateway' => $gateway["code"]), true));
                    $externalOption->setLogo(_MODULE_DIR_ . 'multisafepay/views/images/gateways/' . $locale . '/' . $gateway["code"] . '.png');
                    $externalOption->setAdditionalInformation(Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_DESC'));

                    switch ($gateway['code']) {
                        case "ideal":
                            $externalOption->setForm($this->getIdeal());
                            break;
                        case "payafter":
                            $externalOption->setForm($this->getPayafter());
                            break;
                        case "einvoice":
                            $externalOption->setForm($this->getEinvoice());
                            break;
                    }
                    $payment_options[] = $externalOption;
                }
            }
        }

        //loop through the available MultiSafepay giftcards
        foreach ($this->giftcards as $giftcard) {
            if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard['code']) == 1) {
                $active = false;

                $lang_iso = Language::getIsoById($this->context->cart->id_lang);
                $locale = Language::getLocaleByIso($lang_iso);
                $real_locale = str_replace('-', '_', $locale);

                $supported_languages = array(
                    'nl_NL',
                    'en_GB',
                    'it_IT',
                    'de_DE',
                    'fr_FR',
                    'es_ES'
                );

                if (in_array($real_locale, $supported_languages)) {
                    $locale = $real_locale;
                } else {
                    $locale = "en_GB";
                }


                /*
                 *  start restrictions
                 */
                $billing = new Address($this->context->cart->id_address_invoice);
                $id_country = $billing->id_country;
                $id_currency = $params['cart']->id_currency;
                $id_carrier = $params['cart']->id_carrier;
                $id_shop_group = $params['cart']->id_shop_group;
                $amount = $params['cart']->getOrderTotal(true, Cart::BOTH);


                if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $id_currency) == 'on' && Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $id_shop_group) == "on" && Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $id_carrier) == 'on' && Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $id_country) == 'on') {
                    $active = true;
                }

                /*
                 *  end restrictions
                 */

                if ($active) {
                    $newOption = new PaymentOption();
                    $externalOption = new PaymentOption();
                    $externalOption->setCallToActionText($this->l($giftcard['name']))->setAction($this->context->link->getModuleLink($this->name, 'payment', array('gateway' => $giftcard["code"]), true));
                    $externalOption->setLogo(_MODULE_DIR_ . 'multisafepay/views/images/giftcards/' . $locale . '/' . $giftcard["code"] . '.png');
                    $externalOption->setAdditionalInformation(Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_DESC'));
                    $payment_options[] = $externalOption;
                }
            }
        }
        return $payment_options;
    }



    protected function getActiveGateways()
    {
        $warnings = array();

        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

        if (!empty($multisafepay->getApiKey())) {

            $mspGateways = array_column($multisafepay->gateways->get(), 'id');
            $mspGateways = array_map('strtolower', $mspGateways);

            // Loop all available gateways in this plug-in
            foreach ($this->gateways as $gateway){

                // Skip connect as it is not a real gateway
                if ($gateway["code"] == 'connect' ){
                    continue;
                }

                // check if gateway is enabled in the plug-in
                if ( Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"]) ) {
                    if ( !in_array( $gateway["code"], $mspGateways )) {
                        $warnings[] = sprintf ("%s %s",  $gateway["name"], $this->l('Is not activated in your Multisafepay account'));
                    }
                }
            }
        }
        return $warnings;
    }


    protected function getActiveGiftcards()
    {
        $warnings = array();

        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

        if (!empty($multisafepay->getApiKey())) {

            $mspGateways = array_column($multisafepay->gateways->get(), 'id');
            $mspGateways = array_map('strtolower', $mspGateways);

            // Loop all available gateways in this plug-in
            foreach ($this->giftcards as $giftcard){

                // check if giftcards is enabled in the plug-in
                if ( Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"]) ) {
                    if ( !in_array( $giftcard["code"], $mspGateways )) {
                        $warnings[] = sprintf ("%s %s",  $giftcard["name"], $this->l('Is not activated in your Multisafepay account'));
                    }
                }
            }
        }
        return $warnings;
    }


    protected function getIdeal()
    {
        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));

        if (empty($multisafepay->getApiKey())) {
            return '';
        }

        $issuers = $multisafepay->issuers->get();

        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'ideal'), true),
            'select_bank' => $this->l('Choose your bank'),
            'issuers' => $issuers
        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/issuers.tpl');
    }

    protected function getPayafter()
    {
        $this->context->smarty->assign([
            'action'            => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'payafter'), true),
            'multisafepay_css'  => $this->multisafepay_css,

            'label_birthday'    => $this->l('Birthday'),
            'label_phone'       => $this->l('Phone'),
            'label_bankaccount' => $this->l('Bank account'),

            'birthday'          => $this->getBirthday(),
            'phone'             => $this->getPhoneNumber(),
            'bankaccount'       => '',

            'terms'             => sprintf ( $this->l('By confirming this order you agree with the %s Terms and Conditions %s of MultiFactor'),  '<a href="https://www.multifactor.nl/voorwaarden/betalingsvoorwaarden-consument/" target="_blank">' , '</a>')

        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/payafter.tpl');
    }

    protected function getEinvoice()
    {
        $this->context->smarty->assign([
            'action'            => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'einvoice'), true),
            'multisafepay_css'  => $this->multisafepay_css,

            'label_birthday'    => $this->l('Birthday'),
            'label_phone'       => $this->l('Phone'),
            'label_bankaccount' => $this->l('Bank account'),

            'birthday'          => $this->getBirthday(),
            'phone'             => $this->getPhoneNumber(),
            'bankaccount'       => '',

            'terms'             => sprintf ( $this->l('By confirming this order you agree with the %s Terms and Conditions %s of MultiFactor'),  '<a href="https://www.multifactor.nl/voorwaarden/betalingsvoorwaarden-consument/" target="_blank">' , '</a>')

        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/einvoice.tpl');
    }


    private function getBirthday()
    {
        $birthday = null;

        // Get birthday from Customer
        $customer = new Customer($this->context->cart->id_customer);

        if (Validate::isLoadedObject($customer)) {
            // Prestashop use format YYYY-M-DD, Swap this to DD-MM-YYYY if not 0000-00-00
            if ($customer->birthday != '0000-00-00') {
                $birthday = preg_replace("/(^(\d{4}).(\d{2}).(\d{2}))/", "$4-$3-$2", $customer->birthday);
            }
        }
        return $birthday;
    }

    private function getPhoneNumber()
    {
        $phone = null;

        // Get phonenumber from Customer
        $address  = new Address((int)$this->context->cart->id_address_invoice);
        if (Validate::isLoadedObject($address)) {
            $phone = $address->phone ?: $address->phone_mobile;
        }
        return $phone;
    }

    private function getGender()
    {
        $gender = null;

        // Get Gender from Customer
        $customer = new Customer($this->context->cart->id_customer);
        if (Validate::isLoadedObject($customer)) {
            $gender = $customer->id_gender;
        }
        return $gender;
    }



    protected function checkApiKey()
    {
        $Check = new CheckAPI();
        $error = $Check->myConnection( Tools::getValue('MULTISAFEPAY_API_KEY'), Tools::getValue('MULTISAFEPAY_ENVIRONMENT'));

        return $error;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

}
