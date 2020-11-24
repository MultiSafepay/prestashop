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

class MultisafepayRedirectModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        $cart_id = Tools::getValue('transactionid');

        // Wait maximal 5 seconds to give the system the time to create the order
        $try = 0;
        do {
            sleep(1);
            $order = new Order(Order::getIdByCartId((int)$cart_id));
            $try++;
        } while (empty($order->id_cart) && $try < 5);

        // If order isn't available within 5 seconds, redirect with an error
        if (empty($order->id_cart)) {
            $this->errors[] = $this->module->l('The order is yet not available.', 'return');
            $this->errors[] = $this->module->l('Therefore we cannot redirect you to the order confirmation page.', 'return');
            $this->errors[] = $this->module->l('You are redirected to the order history page instead.', 'return');
            $this->redirectWithNotifications($this->context->link->getPageLink('history', true, null, array()));
        }

        // Redirect to the order confirmation page
        $url = sprintf(
            "index.php?controller=order-confirmation&key=%s&id_cart=%s&id_module=%s&id_order=%s",
            $order->secure_key,
            $order->id_cart,
            Tools::getValue('id_module'),
            $order->id
        );

        Tools::redirect($url);
    }
}
