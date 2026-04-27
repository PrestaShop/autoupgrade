<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_9_0($module)
{
    return $module->registerHookAndSetToTop('dashboardZoneOne');
}
