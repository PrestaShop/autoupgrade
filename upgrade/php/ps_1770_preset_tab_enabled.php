<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Preset enabled new column in tabs to true for all (except for disabled modules)
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1770_preset_tab_enabled()
{
    //First set all tabs enabled
    $result = DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'tab` SET `enabled` = 1'
    );

    //Then search for inactive modules and disable their tabs
    $inactiveModules = DbWrapper::executeS(
        'SELECT `name` FROM `' . _DB_PREFIX_ . 'module` WHERE `active` != 1'
    );
    $moduleNames = [];
    foreach ($inactiveModules as $inactiveModule) {
        $moduleNames[] = '"' . $inactiveModule['name'] . '"';
    }
    if (count($moduleNames) > 0) {
        $result &= DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET `enabled` = 0 WHERE `module` IN (' . implode(',', $moduleNames) . ')'
        );
    }

    return $result;
}
