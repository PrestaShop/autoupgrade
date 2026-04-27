<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1780_add_feature_flag_tab()
{
    $className = 'AdminFeatureFlag';

    $result = DbWrapper::executeS(
        'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = \'AdminAdvancedParameters\''
    );

    if (empty($result)) {
        return;
    }
    if (empty($result[0]['id_tab'])) {
        return;
    }
    $advancedParametersTabId = (int) $result[0]['id_tab'];

    include_once __DIR__ . '/add_new_tab.php';
    add_new_tab_17(
        $className,
        'en:Experimental Feature|fr:Fonctionnalités expérimentales',
        $advancedParametersTabId
    );

    DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'tab` SET `active`= 1, `enabled` = 1 WHERE `class_name` = \'' . $className . '\''
    );
}
