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

namespace MultiSafepay\PrestaShop\models\Api;

use Configuration;
use FileLogger;
use MultiSafepay\PrestaShop\models\Api\Objects\Orders;
use MultiSafepay\PrestaShop\models\Api\Objects\Issuers;
use MultiSafepay\PrestaShop\models\Api\Objects\Gateways;

class MspClient
{

    public $orders;
    public $issuers;
    public $transactions;
    public $gateways;
    protected $api_key;
    public $api_url;
    public $api_endpoint;

    public function __construct()
    {
        $this->orders = new Orders($this);
        $this->issuers = new Issuers($this);
        $this->gateways = new Gateways($this);
    }

    public function initialize($environment, $api)
    {
        $this->setApiKey($api);
        if (!$environment) {
            $this->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->setApiUrl('https://api.multisafepay.com/v1/json/');
        }
    }

    public function setApiUrl($url)
    {
        $this->api_url = trim($url);
    }

    public function setApiKey($api_key)
    {
        $this->api_key = trim($api_key);
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function processAPIRequest($http_method, $api_method, $http_body = null)
    {

        $url = $this->api_url . $api_method;
        $ch = curl_init($url);

        $request_headers = array(
            "Accept: application/json",
            "api_key:" . $this->api_key,
        );

        if ($http_body !== null) {
            $request_headers[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $http_body);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        if (curl_errno($ch)) {
            if (Configuration::get('MULTISAFEPAY_DEBUG')) {
                $logger = new FileLogger(0);
                $logger->setFilename(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'multisafepay' . DIRECTORY_SEPARATOR . 'logs/multisafepay.log');
                $logger->logDebug("Curl Error -------------------------");
                $logger->logDebug("Unable to communicate with the MultiSafepay payment server (" . curl_errno($ch) . "): " . curl_error($ch));
            }
        }
        curl_close($ch);
        return $body;
    }
}
