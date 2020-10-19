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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @return mixed
 */
function upgrade_module_4_7_0()
{
    addNewOrderStatuses();

    // Remove Babygiftcard elements from the configuration
    $sql = "DELETE FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE 'MULTISAFEPAY_GIFTCARD_babygiftcard%'";
    Db::getInstance()->execute($sql);

    // Remove Erotiekbon elements from the configuration
    $sql = "DELETE FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE 'MULTISAFEPAY_GIFTCARD_erotiekbon%'";
    Db::getInstance()->execute($sql);

    // Remove Nationale Verween Cadeaubon elements from the configuration
    $sql = "DELETE FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE 'MULTISAFEPAY_GIFTCARD_nationaleverwencadeaubon%'";
    Db::getInstance()->execute($sql);

    // Remove VVV Bon elements from the configuration
    $sql = "DELETE FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE 'MULTISAFEPAY_GIFTCARD_vvvbon%'";
    return Db::getInstance()->execute($sql);
}

function addNewOrderStatuses()
{
    $statusCode = 'MULTISAFEPAY_OS_AWAITING_BANK_TRANSFER_PAYMENT';

    if (Configuration::get($statusCode)) {
        return;
    }
    $orderState = new OrderState();
    $orderState->name = [];
    foreach (Language::getLanguages() as $language) {
        $orderState->name[$language['id_lang']] = 'MultiSafepay awaiting Bank transfer payment';
    }

    $orderState->send_email = false;
    $orderState->color = '#4169E1';
    $orderState->hidden = false;
    $orderState->delivery = false;
    $orderState->logable = false;
    $orderState->invoice = false;
    $orderState->template = '';
    $orderState->paid = false;
    $orderState->add();
    Configuration::updateValue($statusCode, (int)$orderState->id);
}
