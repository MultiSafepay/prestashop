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
