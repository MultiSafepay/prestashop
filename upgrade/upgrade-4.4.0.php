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
function upgrade_module_4_4_0()
{
    return Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "multisafepay_tokenization` 
        ( `id` INT NOT NULL AUTO_INCREMENT , `customer_id` INT NOT NULL , `order_id` TEXT NOT NULL , `recurring_id` TEXT NULL, `cc_type` VARCHAR(64) NULL ,
        `cc_last4` VARCHAR(4) NULL DEFAULT 0, `cc_expiry_date` VARCHAR(4) NULL DEFAULT 0, `cc_name` VARCHAR(64) NULL DEFAULT 0, PRIMARY KEY (id) )");
}
