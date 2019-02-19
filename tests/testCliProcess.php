<?php
/*
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

// Although no arguments execute the script, you can get some help if requested.
if (isset($argv) && is_array($argv) && in_array('--help', $argv)) {
    displayHelp();
    exit(0);
}

array_shift($argv);
$command = implode(' ', $argv);
$result = 0;
while (!empty($command) && !$result) {
    $lastLine = system('php ' . $command . '  2>&1', $result);

    // if we require to run another command, it will detected here
    $pos = strpos($lastLine, $argv[0]);
    $command = ($pos === false ? null : substr($lastLine, $pos));
}

exit($result);

/**
 * displays the help.
 */
function displayHelp()
{
    echo <<<EOF
PrestaShop upgrade/rollback test

This script can be called to complete a whole process of your shop. This script is currently stored in tests/ as it
is used by automated tests.

testCliProcess.php <Path to cli-upgrade.php/cli-rollback.php etc.> [Options]
------------------
Options
--help            Display this message.

--dir             Tells where the admin directory is.

[UPGRADE]
--channel         Selects what upgrade to run (minor, major etc.)
[ROLLBACK]
--backup          Select the backup to restore. To be found in autoupgrade/backup, in your admin folder.

EOF;
}
