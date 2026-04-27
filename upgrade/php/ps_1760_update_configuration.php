<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Init new configuration values
 */
function ps_1760_update_configuration()
{
    Configuration::updateValue('PS_MAIL_THEME', 'modern');
    Configuration::updateValue('PS_CATALOG_MODE_WITH_PRICES', 0);
}
