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

require __DIR__ . '/vendor/autoload.php';

use MultiSafepay\PrestaShop\helpers\Helper;
use MultiSafepay\PrestaShop\models\Api\MspClient;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Multisafepay extends PaymentModule
{
    protected $_postErrors = array();
    protected $default_locale = 'en_GB';
    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;
    public $countries;
    public $currencies;
    public $carriers;
    public $groups;


    /*
     * This array contains all supported gifcards and is used to generate the configuration an paymentOptions
     */
    public $giftcards = array(
        array("code" => "webshopgft", "name" => "Webshopgiftcard", 'config' => true),
        array("code" => "boekenbon", "name" => "Boekenbon", 'config' => true),
        array("code" => "parfumcadeaukaart", "name" => "Parfumcadeaukaart", 'config' => true),
        array("code" => "yourgift", "name" => "Yourgift", 'config' => true),
        array("code" => "wijncadeau", "name" => "Wijncadeau", 'config' => true),
        array("code" => "gezondheidsbon", "name" => "Gezondheidsbon", 'config' => true),
        array("code" => "fashioncheque", "name" => "Fashioncheque", 'config' => true),
        array("code" => "fashiongft", "name" => "Fashiongiftcard", 'config' => true),
        array("code" => "podium", "name" => "Podium", 'config' => true),
        array("code" => "vvvgiftcrd", "name" => "VVV Cadeaukaart", 'config' => true),
        array("code" => "sportenfit", "name" => "Sport en Fit", 'config' => true),
        array("code" => "goodcard", "name" => "Goodcard", 'config' => true),
        array("code" => "nationaletuinbon", "name" => "Nationale tuinbon", 'config' => true),
        array("code" => "beautyandwellness", "name" => "Beauty and wellness", 'config' => true),
        array("code" => "fietsenbon", "name" => "Fietsenbon", 'config' => true),
        array("code" => "wellnessgiftcard", "name" => "Wellnessgiftcard", 'config' => true),
        array("code" => "winkelcheque", "name" => "Winkelcheque", 'config' => true),
        array("code" => "givacard", "name" => "Givacard", 'config' => true),
        array("code" => "good4fun", "name" => "Good4fun Giftcard", 'config' => true),
    );

    /*
     * This array contains all supported paymentmethods and is used to generate the configuration an paymentOptions
     */
    public $gateways = array(
        array("code" => "ideal", "name" => "iDEAL", 'config' => true),
        array("code" => "dotpay", "name" => "Dotpay", 'config' => true),
        array("code" => "payafter", "name" => "Betaal na Ontvangst", 'config' => true),
        array("code" => "einvoice", "name" => "E-Invoicing", 'config' => true),
        array("code" => "klarna", "name" => "Klarna - Buy now, pay later", 'config' => true),
        array("code" => "mistercash", "name" => "Bancontact", 'config' => true),
        array("code" => "visa", "name" => "Visa", 'config' => true),
        array("code" => "eps", "name" => "EPS", 'config' => true),
        array("code" => "mastercard", "name" => "Mastercard", 'config' => true),
        array("code" => "banktrans", "name" => "Bank transfer", 'config' => true),
        array("code" => "psafecard", "name" => "Paysafecard", 'config' => true),
        array("code" => "maestro", "name" => "Maestro", 'config' => true),
        array("code" => "paypal", "name" => "PayPal", 'config' => true),
        array("code" => "giropay", "name" => "Giropay", 'config' => true),
        array("code" => "directbank", "name" => "SOFORT Banking", 'config' => true),
        array("code" => "inghome", "name" => "ING Home'Pay", 'config' => true),
        array("code" => "belfius", "name" => "Belfius", 'config' => true),
        array("code" => "trustpay", "name" => "TrustPay", 'config' => true),
        array("code" => "kbc", "name" => "KBC", 'config' => true),
        array("code" => "dirdeb", "name" => "Direct Debit", 'config' => true),
        array("code" => "alipay", "name" => "Alipay", 'config' => true),
        array("code" => "connect", "name" => "MultiSafepay", 'config' => true),
        array("code" => "amex", "name" => "American Express", 'config' => true),
        array("code" => "santander", "name" => "Betaal per Maand", 'config' => true),
        array("code" => "afterpay", "name" => "AfterPay", 'config' => true),
        array("code" => "trustly", "name" => "Trustly", 'config' => true),
        array("code" => "idealqr", "name" => "iDEAL QR", 'config' => true),
        array("code" => "dbrtp", "name" => "Request to Pay", 'config' => true),
        array("code" => "applepay", "name" => "Apple Pay", 'config' => true),
        array("code" => "in3", "name" => "in3", 'config' => true),
        array("code" => "cbc", "name" => "CBC", 'config' => true),

    );

    public function __construct()
    {
        $this->name = 'multisafepay';
        $this->tab = 'payments_gateways';
        $this->version = '4.8.0';
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
            $this->gateways[$i]['sort'] = (int) Configuration::get('MULTISAFEPAY_GATEWAY_' . $this->gateways[$i]['code'] . '_SORT');
        }

        usort($this->gateways, function ($a, $b) {
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
         * Sort the giftcards based by provided sort order configuration value
         */
        for ($i = 0; $i < count($this->giftcards); $i ++) {
            $this->giftcards[$i]['sort'] = (int) Configuration::get('MULTISAFEPAY_GIFTCARD_' . $this->giftcards[$i]['code'] . '_SORT');
        }

        usort($this->giftcards, function ($a, $b) {
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
        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('actionOrderSlipAdd')
            || !$this->registerHook('displayPDFInvoice')
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
            'chargeback' => array(
                'name' => 'chargeback',
                'send_mail' => true,
                'color' => '#ec2e15',
                'invoice' => false,
                'template' => '',
                'paid' => false,
                'logable' => false
            ),
            'awaiting_bank_transfer_payment' => array(
                'name' => 'awaiting Bank transfer payment',
                'send_mail' => false,
                'color' => '#4169E1',
                'invoice' => false,
                'template' => '',
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
        $this->addTokenizationTable();
        return true;
    }


    /**
     * @param $params
     * @return string
     */
    public function hookDisplayPDFInvoice($params)
    {
        $invoice = $params['object'];
        $order = new Order($invoice->id_order);
        $bankDetails = '';

        $helper = new Helper;
        if ($order->payment === $helper->getPaymentMethod('banktrans')) {
            $bankDetails = $this->getInvoiceBankDetails($order->id);
        }

        return $bankDetails;
    }


    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path . 'views/js/multisafepay_front.js');
        Media::addJsDefL('confirm_token_deletion', $this->l('Are you sure you want to delete '));
    }



    protected function initializeConfig()
    {
        $default_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');
        $this->groups = Group::getGroups($this->context->language->id);
        $this->carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);

        foreach ($this->giftcards as $giftcard) {
            Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $default_currency, 'on');
            Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_' . $default_country, 'on');

            foreach ($this->groups as $group) {
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group['id_group'], 'on');
            }

            foreach ($this->carriers as $carrier) {
                Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_reference'], 'on');
            }
        }
        foreach ($this->gateways as $gateway) {
            Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $default_currency, 'on');
            Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $default_country, 'on');

            foreach ($this->groups as $group) {
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group'], 'on');
            }

            foreach ($this->carriers as $carrier) {
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_reference'], 'on');
            }
        }

        /*
        * Initialize Betaalplan minimum/maximum amounts
        */
        Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . 'santander' . '_MIN_AMOUNT', 250);
        Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . 'santander' . '_MAX_AMOUNT', 1000);
    }

    public function uninstall()
    {
        //@TODO: Also add Configuration::deleteByName for each gateway/giftcard + restrictions

        $this->unregisterHook('paymentOptions');
        $this->unregisterHook('paymentReturn');
        $this->unregisterHook('actionOrderStatusPostUpdate');
        $this->unregisterHook('actionFrontControllerSetMedia');
        $this->unregisterHook('displayPDFInvoice');
        $this->unregisterHook('actionOrderSlipAdd');


        Configuration::deleteByName('MULTISAFEPAY_API_KEY');
        Configuration::deleteByName('MULTISAFEPAY_DEBUG');
        Configuration::deleteByName('MULTISAFEPAY_ENVIRONMENT');
        Configuration::deleteByName('MULTISAFEPAY_TIME_ACTIVE');
        Configuration::deleteByName('MULTISAFEPAY_TIME_UNIT');
        Configuration::deleteByName('MULTISAFEPAY_ENABLE_TOKEN');

        return parent::uninstall();
    }

    /**
     * @param $id
     * @return bool
     */
    private function isShippingStatus($id)
    {
        return ((int)$id === (int)Configuration::get('PS_OS_SHIPPING'));
    }

    /**
     * @param \Order $order
     * @return bool
     */
    private function isMultiSafepayOrder(Order $order)
    {
        return $order->module && $order->module === 'multisafepay';
    }

    /**
     * @param array $params
     */
    public function hookActionOrderStatusPostUpdate(array $params)
    {
        if (!$this->isShippingStatus($params['newOrderStatus']->id)) {
            return;
        }
        $order = new Order((int)$params['id_order']);
        if (!$this->isMultiSafepayOrder($order)) {
            return;
        }

        $carrier = new Carrier((int)$params['cart']->id_carrier);
        $shipData = array(
            'tracktrace_code' => $order->getWsShippingNumber(),
            'carrier' => $carrier->name,
            'ship_date' => date('Y-m-d H:i:s'),
            'reason' => 'Shipped'
        );
        $endpoint = 'orders/' . $params['cart']->id;

        $multisafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multisafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));
        $multisafepay->orders->patch($shipData, $endpoint);
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
            Configuration::updateValue('MULTISAFEPAY_ENABLE_TOKEN', Tools::getValue('MULTISAFEPAY_ENABLE_TOKEN'));
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
                    Configuration::updateValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_reference'], Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_reference']));
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
                    Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_reference'], Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_reference']));
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

        $multisafepay_module_dir = $this->_path;

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
            'multisafepay_module_dir' => $multisafepay_module_dir,
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
            'title' => $this->l('MultiSafepay configuration'),
            'content' => $this->getMainConfiguration()
        );

        $tabs[] = array(
            'id' => 'gateway_configuration',
            'title' => $this->l('Payment Methods'),
            'content' => $this->getGateways()
        );

        $tabs[] = array(
            'id' => 'giftcard_configuration',
            'title' => $this->l('Giftcards'),
            'content' => $this->getGiftcards()
        );

        $tabs[] = array(
            'id' => 'gateway_restrictions_configuration',
            'title' => $this->l('Payment Restrictions'),
            'content' => $this->getGatewayRestrictions()
        );

        $tabs[] = array(
            'id' => 'giftcard_restrictions_configuration',
            'title' => $this->l('Giftcard Restrictions'),
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
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group']) == 'on') {
                    $this->gateways[$key]['group'][$group['id_group']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group['id_group']);
                }
            }

            foreach ($this->carriers as $carrier) {
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_reference']) == 'on') {
                    $this->gateways[$key]['carrier'][$carrier['id_reference']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_' . $carrier['id_reference']);
                }
            }

            foreach ($this->countries as $country) {
                if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $country['id_country']) == 'on') {
                    $this->gateways[$key]['country'][$country['id_country']] = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_' . $country['id_country']);
                }
            }
        }

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
                if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_reference']) == 'on') {
                    $this->giftcards[$key]['carrier'][$carrier['id_reference']] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_' . $carrier['id_reference']);
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
            'MULTISAFEPAY_TIME_UNIT' => Tools::getValue('MULTISAFEPAY_TIME_UNIT', Configuration::get('MULTISAFEPAY_TIME_UNIT')),
            'MULTISAFEPAY_ENABLE_TOKEN' => Tools::getValue('MULTISAFEPAY_ENABLE_TOKEN', Configuration::get('MULTISAFEPAY_ENABLE_TOKEN'))
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
                        'hint' => $this->l('If enabled live transactions will be processed, otherwise test-transactions'),
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
                        'hint' => $this->l('The MultiSafepay API key can be found within your Multisafepay website configuration using MultiSafepay Control'),
                        'name' => 'MULTISAFEPAY_API_KEY',
                        'required' => true
                    ),

                    array(
                        'type' => 'text',
                        'label'=>  $this->l('Time before a transaction will expire'),
                        'hint' => $this->l('The transaction will expire after the given time.'),
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
                        'hint' => $this->l('When enabled, all API requests and responses are logged within the modules/multisafepay/logs/ folder'),
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
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Tokenization'),
                        'hint' => $this->l('Enable the Tokenization feature'),
                        'name' => 'MULTISAFEPAY_ENABLE_TOKEN',
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
                    )
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
            $this->gateways[$key]['logo'] = $this->getLogo($gateway['code'], 'gateways');
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
            $this->giftcards[$key]['title'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_TITLE');
            $this->giftcards[$key]['sort'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_SORT');
            $this->giftcards[$key]['desc'] = Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_DESC');
            $this->giftcards[$key]['logo'] = $this->getLogo($giftcard['code'], 'giftcards');
        }

        $template_vars = array(
            'giftcards' => $this->giftcards,
            'save' => $this->l('Save'),
            'enable' => $this->l('On'),
            'disable' => $this->l('Off'),
            'description' => $this->l('Frontend description'),
            'configuration' => $this->l('configuration'),
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

        $groups = [];
        if (isset($this->context->customer)) {
            $groups = $this->context->customer->getGroups();
        }

        $billing        = new Address($this->context->cart->id_address_invoice);
        $id_country     = $billing->id_country;
        $id_currency    = $params['cart']->id_currency;
        $carrier        = new Carrier((int) $params['cart']->id_carrier);
        $carrierIdReference = $carrier->id_reference;

        $amount         = $params['cart']->getOrderTotal(true, Cart::BOTH);
        $isVirtualCart  = $params['cart']->isVirtualCart();

        // loop through the available MultiSafepay gateways
        foreach ($this->gateways as $gateway) {
            if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway['code']) == 1) {
                $active = false;

                $activeGroup = false;
                foreach ($groups as $group) {
                    if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_GROUP_' . $group) == 'on') {
                        $activeGroup = true;
                        break;
                    }
                }

                if ($activeGroup === true &&
                    Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CURRENCY_' . $id_currency) == 'on' &&
                    Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_COUNTRY_'  . $id_country) == 'on' &&
                   (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_CARRIER_'  . $carrierIdReference) == 'on' || $isVirtualCart || empty($carrierIdReference))
                    ) {
                    $active = true;
                }

                $min_amount = floatval(Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MIN_AMOUNT'));
                $max_amount = floatval(Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_MAX_AMOUNT'));

                if ((!empty($min_amount) && $amount < $min_amount) || (!empty($max_amount) && $amount > $max_amount)) {
                    $active = false;
                }

                if ($active) {
                    if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE')) {
                        $title = Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_TITLE');
                    } else {
                        $title = $gateway['name'];
                    }

                    $logo = $this->getLogo($gateway['code'], 'gateways');
                    //$newOption = new PaymentOption();
                    $externalOption = new PaymentOption();
                    $externalOption->setCallToActionText($this->l($title));
                    $externalOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array('gateway' => $gateway["code"]), true));
                    $externalOption->setLogo($logo);
                    $externalOption->setAdditionalInformation(Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway["code"] . '_DESC'));

                    switch ($gateway['code']) {
                        case "ideal":
                            $externalOption->setForm($this->getIdeal());
                            break;
                        case "afterpay":
                            $externalOption->setForm($this->getAfterPay());
                            break;
                        case "in3":
                            $externalOption->setForm($this->getIn3());
                            break;
                        case "payafter":
                            $externalOption->setForm($this->getPayafter());
                            break;
                        case "einvoice":
                            $externalOption->setForm($this->getEinvoice());
                            break;
                        case "applepay":
                            $this->context->smarty->assign([
                                'action' => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'applepay'), true),
                            ]);

                            $externalOption->setForm($this->context->smarty->fetch('module:multisafepay/views/templates/front/applepay.tpl'));
                            break;
                        case "amex":
                        case "visa":
                        case "mastercard":
                            if (Context::getContext()->customer->isLogged()) {
                                $externalOption->setForm($this->getTokenization($gateway['code']));
                            }
                            break;
                        default:
                            $externalOption->setForm($this->getDefault($gateway['code']));
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

                $activeGroup = false;
                foreach ($groups as $group) {
                    if (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_GROUP_' . $group) == 'on') {
                        $activeGroup = true;
                        break;
                    }
                }

                if ($activeGroup === true &&
                    Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CURRENCY_' . $id_currency) == 'on' &&
                    Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_COUNTRY_'  . $id_country) == 'on' &&
                    (Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_CARRIER_'  . $carrierIdReference) == 'on' || $isVirtualCart)
                    ) {
                    $active = true;
                }

                if ($active) {
                    $logo = $this->getLogo($giftcard['code'], 'giftcards');

                    $newOption = new PaymentOption();
                    $externalOption = new PaymentOption();
                    $externalOption->setCallToActionText($this->l($giftcard['name']))->setAction($this->context->link->getModuleLink($this->name, 'payment', array('gateway' => $giftcard["code"]), true));
                    $externalOption->setLogo($logo);
                    $externalOption->setAdditionalInformation(Configuration::get('MULTISAFEPAY_GIFTCARD_' . $giftcard["code"] . '_DESC'));
                    $externalOption->setForm($this->getDefault($giftcard['code']));
                    $payment_options[] = $externalOption;
                }
            }
        }
        return $payment_options;
    }

    /**
     * @return array
     */
    protected function getActiveGateways()
    {
        $warnings = array();
        $paymentMethods = $this->getPaymentMethods();

        foreach ($this->gateways as $gateway) {
            // Skip connect as it is not a real gateway
            if ($gateway['code'] === 'connect') {
                continue;
            }

            if (Tools::getValue('MULTISAFEPAY_GATEWAY_' . $gateway['code'])) {
                if (!in_array($gateway['code'], $paymentMethods)) {
                    $warnings[] = sprintf('%s %s', $gateway['name'], $this->l('Is not activated in your MultiSafepay account'));
                }
            }
        }
        return $warnings;
    }

    /**
     * @return array
     */
    protected function getActiveGiftcards()
    {
        $warnings = array();
        $paymentMethods = $this->getPaymentMethods();

        foreach ($this->giftcards as $giftcard) {
            if (Tools::getValue('MULTISAFEPAY_GIFTCARD_' . $giftcard['code'])) {
                if (!in_array($giftcard['code'], $paymentMethods)) {
                    $warnings[] = sprintf('%s %s', $giftcard['name'], $this->l('Is not activated in your MultiSafepay account'));
                }
            }
        }
        return $warnings;
    }

    /**
     * @return array
     */
    private function getPaymentMethods()
    {
        $multisafepay = new MspClient();
        $multisafepay->initialize(
            Configuration::get('MULTISAFEPAY_ENVIRONMENT'),
            Configuration::get('MULTISAFEPAY_API_KEY')
        );

        $paymentMethods = array();
        if (!empty($multisafepay->getApiKey())) {
            foreach ($multisafepay->gateways->get() as $gateway) {
                $paymentMethods[] = strtolower($gateway->id);
            }
        }
        return $paymentMethods;
    }

    /**
     * @param $code
     * @param $type
     * @return string
     */
    private function getLogo($code, $type)
    {
        $logo = $this->getUrlLogo($code, $type, $this->getLocale());
        if (!file_exists(_PS_ROOT_DIR_ . $logo)) {
            $logo = $this->getUrlLogo($code, $type, $this->default_locale);
        }
        return $logo;
    }

    /**
     * @param $code
     * @param $type
     * @param $locale
     * @return string
     */
    private function getUrlLogo($code, $type, $locale)
    {
        $logo = _MODULE_DIR_ . 'multisafepay/views/images/' . $type . '/' . $locale . '/' . $code . '.png';
        return $logo;
    }

    /**
     * @return string|string[]
     */
    private function getLocale()
    {
        return str_replace('-', '_', $this->context->language->locale);
    }

    protected function getDefault($payment)
    {
        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => $payment), true),
            'gateway' => $payment
        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/default.tpl');
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

    protected function getTokenization($payment)
    {
        $multisafepay_module_dir = $this->_path;

        $token_enabled = Configuration::get('MULTISAFEPAY_ENABLE_TOKEN');

        if ($token_enabled) {
            $this->context->smarty->assign([
                'multisafepay_module_dir' => $multisafepay_module_dir,
                'label_creditcard' => $this->l('Save your creditcard details for a next purchase.'),
                'label_description' => $this->l('Provide a name for the creditcard *'),
                'action' => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => $payment), true),
                'gateway' => $payment,
                'label_dropdown' => $this->l('Choose your creditcard...'),
                'tokens' => $this->getRecurringsFromCustomerId($this->context->cart->id_customer),
                'saved_tokens' => $this->tokensSaved(),
                'saved_gateways' => $this->tokenGateways(),
            ]);

            return $this->context->smarty->fetch('module:multisafepay/views/templates/front/token.tpl');
        }
    }

    protected function getAfterPay()
    {
        $multisafepay_module_dir = $this->_path;

        $this->context->smarty->assign([
            'action'            => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'afterpay'), true),
            'multisafepay_module_dir'  => $multisafepay_module_dir,

            'label_gender'       => $this->l('Gender'),
            'label_birthday'    => $this->l('Birthday'),
            'label_phone'       => $this->l('Phone'),
            'label_mr'          => $this->l('Mr'),
            'label_mrs'         => $this->l('Mrs'),
            'label_miss'        => $this->l('Miss'),

            'gender'            => $this->getGender(),
            'birthday'          => $this->getBirthday(),
            'phone'             => $this->getPhoneNumber(),

            'terms'             => sprintf($this->l('By confirming this order you agree with the %s Terms and Conditions %s of AfterPay'), '<a href="https://www.afterpay.nl/en/about/pay-with-afterpay/payment-conditions" target="_blank">', '</a>')

        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/afterpay.tpl');
    }


    /**
     * @return mixed
     */
    protected function getIn3()
    {
        $multisafepay_module_dir = $this->_path;

        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'in3'), true),
            'multisafepay_module_dir' => $multisafepay_module_dir,

            'label_birthday' => $this->l('Birthday'),
            'label_phone' => $this->l('Phone'),
            'label_gender' => $this->l('Gender'),

            'birthday' => $this->getBirthday(),
            'phone' => $this->getPhoneNumber(),
            'gender' => $this->getGender(),
        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/in3.tpl');
    }

    protected function getPayafter()
    {
        $multisafepay_module_dir = $this->_path;

        $this->context->smarty->assign([
            'action'            => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'payafter'), true),
            'multisafepay_module_dir'  => $multisafepay_module_dir,

            'label_birthday'    => $this->l('Birthday'),
            'label_phone'       => $this->l('Phone'),
            'label_bankaccount' => $this->l('Bank account'),

            'birthday'          => $this->getBirthday(),
            'phone'             => $this->getPhoneNumber(),
            'bankaccount'       => '',

            'terms'             => sprintf($this->l('By confirming this order you agree with the %s Terms and Conditions %s of MultiFactor'), '<a href="https://www.multifactor.nl/voorwaarden/betalingsvoorwaarden-consument/" target="_blank">', '</a>')

        ]);

        return $this->context->smarty->fetch('module:multisafepay/views/templates/front/payafter.tpl');
    }

    protected function getEinvoice()
    {
        $multisafepay_module_dir = $this->_path;

        $this->context->smarty->assign([
            'action'            => $this->context->link->getModuleLink($this->name, 'payment', array('payment' => 'einvoice'), true),
            'multisafepay_module_dir'  => $multisafepay_module_dir,

            'label_birthday'    => $this->l('Birthday'),
            'label_phone'       => $this->l('Phone'),
            'label_bankaccount' => $this->l('Bank account'),

            'birthday'          => $this->getBirthday(),
            'phone'             => $this->getPhoneNumber(),
            'bankaccount'       => '',

            'terms'             => sprintf($this->l('By confirming this order you agree with the %s Terms and Conditions %s of MultiFactor'), '<a href="https://www.multifactor.nl/voorwaarden/betalingsvoorwaarden-consument/" target="_blank">', '</a>')

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

    /**
     * @return string|null
     */
    private function getGender()
    {
        $id_gender = null;

        // Get Gender from Customer
        $customer = new Customer($this->context->cart->id_customer);
        if (Validate::isLoadedObject($customer)) {
            $id_gender = $customer->id_gender;
        }
        return $id_gender;
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


    protected function addTokenizationTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "multisafepay_tokenization` 
        ( `id` INT NOT NULL AUTO_INCREMENT , `customer_id` INT NOT NULL , `order_id` TEXT NOT NULL , `recurring_id` TEXT NULL, `cc_type` VARCHAR(64) NULL ,
        `cc_last4` VARCHAR(4) NULL DEFAULT 0, `cc_expiry_date` VARCHAR(4) NULL DEFAULT 0, `cc_name` VARCHAR(64) NULL DEFAULT 0, PRIMARY KEY (id) )";

        Db::getInstance()->execute($sql);
    }

    protected function getRecurringsFromCustomerId($customer_id)
    {
        if (!$this->context->customer->logged) {
            return false;
        }

        $tokens = Db::getInstance()->ExecuteS("SELECT id, cc_name, recurring_id, cc_type  FROM " . _DB_PREFIX_ . "multisafepay_tokenization WHERE customer_id = {$customer_id} AND recurring_id != 'NULL'");

        return $tokens;
    }

    protected function tokensSaved()
    {
        $saved_tokens = $this->getRecurringsFromCustomerId($this->context->cart->id_customer);

        return empty((array)$saved_tokens);
    }

    protected function tokenGateways()
    {
        $tokens = $this->getRecurringsFromCustomerId($this->context->cart->id_customer);

        $gateways = [];

        foreach ($tokens as $token) {
            if (!in_array(strtolower($token['cc_type']), $gateways)) {
                $gateways[] = strtolower($token['cc_type']);
            }
        }

        return $gateways;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function hookActionOrderSlipAdd($params = [])
    {
        // Do not refund at MultiSafepay when vouchers are generated
        if (Tools::isSubmit('generateDiscountRefund')) {
            return false;
        }

        if ($params['order']->module !== 'multisafepay') {
            return false;
        }

        $refundMethod = $this->getRefundMethod();

        if ($refundMethod === 'cancelQuantity' || $refundMethod === null) {
            $message = $this->l('Partial refund was successfully created, but failed to partially refund at MultiSafepay');
            $message .= ': ';
            $message .= $this->l('We currently do not support that method of refunding. The method we do support is \'Partial refund\'');

            $this->context->controller->errors[] = $message;
            return false;
        }

        try {
            $this->partialRefundButton($params);
        } catch (\Exception $e) {
            $message = $this->l('Partial refund was successfully created, but failed to partially refund at MultiSafepay');
            $message .= ': ' . $e->getMessage();
            $this->context->controller->errors[] = $message;
            return false;
        }

        return true;
    }

    /**
     * @param array $params
     * @throws Exception
     */
    public function partialRefundButton($params = [])
    {
        $order = $params['order'];
        $productList = $params['productList'];

        $multiSafepay = new MspClient();
        $environment = Configuration::get('MULTISAFEPAY_ENVIRONMENT');
        $multiSafepay->initialize($environment, Configuration::get('MULTISAFEPAY_API_KEY'));
        $transaction = $multiSafepay->orders->get('orders', $order->id_cart);
        $params['gateway'] = $transaction->payment_details->type;

        if (!$this->validateParams($params)) {
            throw new \Exception('The given data did not pass the validation');
        }

        $refundArray['description'] = 'Refund for order ' . $order->id_cart;
        $refundArray = $this->getSimpleRefundArrayData($order, $productList);

        $multiSafepay->orders->post($refundArray, 'orders/'.$order->id_cart.'/refunds');
        $result = $multiSafepay->orders->getResult();

        if (!$result->success) {
            throw new Exception($result->error_code .' : ' . $result->error_info);
        }
    }

    /**
     * @param Order $order
     * @param array $productList
     * @return mixed
     * @throws Exception
     */
    public function getSimpleRefundArrayData(Order $order, $productList = [])
    {
        $currency = new Currency($order->id_currency);
        $refund['currency'] = $currency->iso_code;
        $refund['amount'] = $this->getRefundSimpleAmount($productList);
        $refund['amount'] += $this->getRefundShippingAmount();
        $refund['amount'] = round($refund['amount'] * 100);

        return $refund;
    }

    /**
     * @param array $params
     * @return bool
     */
    private function validateParams($params = [])
    {
        // We do not refund when order contains discounts
        if (Tools::getValue('order_discount_price')) {
            return false;
        }
        if (in_array($params['gateway'], ['KLARNA', 'PAYAFTER', 'EINVOICE', 'AFTERPAY'])) {
            return false;
        }

        if (!isset($params['order']) && !$params['order'] instanceof Order) {
            return false;
        }

        if (!isset($params['productList']) && is_array($params['productList'])) {
            return false;
        }

        if (!isset($params['qtyList']) && is_array($params['qtyList'])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $productList
     * @return int|mixed
     * @throws Exception
     */
    private function getRefundSimpleAmount($productList = [])
    {
        $refund_amount = 0;
        foreach ($productList as $productListItem) {
            $refund_amount += $productListItem['amount'];
        }
        return $refund_amount;
    }

    /**
     * @return float
     */
    private function getRefundShippingAmount()
    {
        return (float) str_replace(',', '.', Tools::getValue('partialRefundShippingCost'));
    }

    /**
     * @param $orderId
     * @return string
     */
    private function getInvoiceBankDetails($orderId)
    {
        $invoiceBankDetails = '';

        $helper = new Helper;
        $message = $helper->getCustomerMessage($orderId, 'destination_holder_name');

        if ($message) {
            $bankDetails = json_decode($message);

            $invoiceBankDetails = '<table>';
            $invoiceBankDetails .= '<tr><td colspan="2"><strong>' . $this->l('Payment information') . '</strong></td></tr>';
            $invoiceBankDetails .= '<tr><td>' . $this->l('Name') . ':</td><td>' . $bankDetails->destination_holder_name . '</td></tr>';
            $invoiceBankDetails .= '<tr><td>' . $this->l('IBAN') . ':</td><td>' . $bankDetails->destination_holder_iban . '</td></tr>';
            $invoiceBankDetails .= '<tr><td>' . $this->l('Reference number') . ':</td><td>' . $bankDetails->reference . '</td></tr>';
            $invoiceBankDetails .= '</table>';
        }

        return $invoiceBankDetails;
    }

    /**
     * Get the name of the refund method. Return null if refund method is unknown
     *
     * @return string|null
     */
    public function getRefundMethod()
    {
        if (array_sum(Tools::getValue('cancelQuantity'))) {
            return 'cancelQuantity';
        }

        if (array_sum(Tools::getValue('partialRefundProduct'))) {
            return 'partialRefundProduct';
        }

        if (array_sum(Tools::getValue('partialRefundProductQuantity'))) {
            return 'partialRefundProductQuantity';
        }

        return null;
    }
}
