<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
function install_ps_apiresources()
{
    if (class_exists('Ps_Apiresources')) {
        $module = new Ps_Apiresources();
        $module->install();
    }
}
