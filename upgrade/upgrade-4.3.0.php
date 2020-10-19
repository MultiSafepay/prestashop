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
 * @param $module
 * @return bool
 */
function upgrade_module_4_3_0($module)
{
    // Convert use of id_carrier to id_reference when upgrading plugin
    $carriers = Carrier::getCarriers((int)Context::getContext()->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
    foreach ($carriers as $carrier) {
        foreach ($module->gateways as $gateway) {
            if (Configuration::get('MULTISAFEPAY_GATEWAY_' . $gateway['code'] . '_CARRIER_' . $carrier['id_carrier']) === 'on') {
                Configuration::updateValue('MULTISAFEPAY_GATEWAY_' . $gateway['code'] . '_CARRIER_' . $carrier['id_reference'], 'on');
            }
        }
    }
    return true;
}
