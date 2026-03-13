<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
function install_ps_distributionapiclient()
{
    if (class_exists('Ps_Distributionapiclient')) {
        $module = new Ps_Distributionapiclient();
        $module->install();
    }
}
