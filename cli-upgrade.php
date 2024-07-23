<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\AutoUpgrade\ErrorHandler;

if (PHP_SAPI !== 'cli') {
    echo 'This script must be called from CLI';
    exit(1);
}

// Although no arguments execute the script, you can get some help if requested.
if (isset($argv) && is_array($argv) && in_array('--help', $argv)) {
    displayHelp();
    exit(0);
}

require_once realpath(dirname(__FILE__) . '/../../modules/autoupgrade') . '/ajax-upgradetabconfig.php';
$container = autoupgrade_init_container(dirname(__FILE__));

$logger = new PrestaShop\Module\AutoUpgrade\Log\StreamedLogger();
$container->setLogger($logger);
(new ErrorHandler($logger))->enable();
$controller = new \PrestaShop\Module\AutoUpgrade\Task\Runner\AllUpgradeTasks($container);
$controller->setOptions(getopt('', ['action::', 'channel::', 'data::']));
$controller->init();
$exitCode = $controller->run();

$options = getopt('', ['chain::']);
$chain = true;

if (isset($options['chain'])) {
    $chain = $options['chain'] === false || filter_var($options['chain'], FILTER_VALIDATE_BOOLEAN);
}

if ($chain && strpos($logger->getLastInfo(), 'cli-upgrade.php') !== false) {
    $new_string = str_replace('INFO - $ ', '', $logger->getLastInfo());
    system('php ' . $new_string, $exitCode);
}

exit($exitCode);

/**
 * displays the help.
 */
function displayHelp(): void
{
    echo <<<EOF
PrestaShop upgrade by CLI

cli-upgrade.php [Options]
------------------
Options
--help            Display this message.
--dir             Tells where the admin directory is.
--chain           Allows you to chain upgrade commands. True by default.
--action          Advanced users only. Sets the step you want to start from (Default: `UpgradeNow`)
--channel         Selects what upgrade to run (minor, major etc.)

EOF;
}
