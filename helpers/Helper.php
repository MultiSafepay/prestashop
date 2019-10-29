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

namespace MultiSafepay\PrestaShop\helpers;

class Helper extends \Module
{
    /**
     * @param $gateway_code
     * @return string
     */
    public function getPaymentMethod($gateway_code)
    {
        $result = 'unknown';
        $gateway_code = strtolower($gateway_code);

        if ($gateway_code == 'coupon') {
            $result = 'Coupon';
        } else {
            $msp = new \Multisafepay();
            foreach ($msp->gateways as $gateway) {
                if ($gateway['code'] == $gateway_code) {
                    $result = $gateway['name'];
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param $bankDetails
     * @param int $cartId
     */
    public function saveBankTransferDetails($bankDetails, $cartId)
    {
        $msg = new \Message();
        $msg->message = json_encode($bankDetails);
        $msg->id_cart = (int)$cartId;
        $msg->add();
    }


    /**
     * @param $orderId
     * @param $searchString
     * @return string
     */
    public function getCustomerMessage($orderId, $searchString)
    {
        $result = '';
        $customerMessage = new \CustomerMessageCore();
        $messages = $customerMessage->getMessagesByOrderId($orderId);

        foreach ($messages as $message) {
            if (strpos($message['message'], $searchString)) {
                $result = $message['message'];
                break;
            }
        }
        return $result;
    }
}
