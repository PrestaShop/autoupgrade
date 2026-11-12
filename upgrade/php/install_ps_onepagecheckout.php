<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
function install_ps_onepagecheckout()
{
    if (class_exists('Ps_Onepagecheckout')) {
        $module = new Ps_Onepagecheckout();
        $module->install();
    }
}
