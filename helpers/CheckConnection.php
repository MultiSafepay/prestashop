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


class CheckAPI extends Module
{

    public function myConnection ($api=null, $mode=null)
    {

        if (empty ($api)) {
            return;
        }

        // Test if current mode works
        $errors = $this->testConnection($api, $mode);
        switch ($errors) {
            case '':
                return;
            case '1004':
                return $this->l(sprintf ('Error %s, probably the website is not active within your MultiSafepay account.', $errors),  'multisafepay');
        }

        // Test if opposite mode works. In that case the API is correct, but the mode is incorrect
        $errors = $this->testConnection($api, !$mode);
        if (empty ($errors)) {
            return $this->l(sprintf ('This API-Key belongs to a %s-account.', $mode ? $this->l('TEST') : $this->l('LIVE') ), 'multisafepay');
        }

        // Error occured. In most cases a 1032 - Wrong API key
        switch ($errors) {
            case '1032':
                return $this->l(sprintf ('Error %s, probably the API-Key is not correct.', $errors),  'multisafepay');
            default:
                return $this->l(sprintf ('Error %s, unknown error. Please contact MultiSafepay,', $errors),  'multisafepay');
         }
    }

    private function testConnection ($api, $mode){

        $test_order = array(
            "type"         => 'redirect',
            "order_id"     => 'Check Connection-'. time(),
            "currency"     => 'EUR',
            "amount"       => 1,
            "description"  => 'Check Connection-'. time()
        );

        $msp = new MspClient();
        $msp->initialize($mode, $api);

        try {
            $msp->orders->post($test_order);
            $msp->orders->getPaymentLink();
        } catch (Exception $e) {
            return ( $e->getMessage());
        }

        // Connection successful
        if ($msp->orders->result->success) {
            return;
        }

        // Connection not successful
        if (isset ($msp->orders->result->error_code)) {
            return ($msp->orders->result->error_code);
        }else{
            return ('unknown');
        }
    }
}