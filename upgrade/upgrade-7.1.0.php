<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @return bool
 */
function upgrade_module_7_1_0($module)
{
    // Create the 'AdminAutoupgradeAjax' tab configuration
    if (!\Tab::getIdFromClassName('AdminAutoupgradeAjax')) {
        $ajaxTab = new \Tab();
        $ajaxTab->class_name = 'AdminAutoupgradeAjax';
        $ajaxTab->module = 'autoupgrade';
        $ajaxTab->id_parent = -1;

        foreach (Language::getLanguages(false) as $lang) {
            $ajaxTab->name[(int) $lang['id_lang']] = 'Update assistant';
        }
        if (!$ajaxTab->save()) {
            return false;
        }
    }

    $module->registerHook('displayBackOfficeHeader');
    $module->registerHook('displayBackOfficeEmployeeMenu');

    return true;
}
