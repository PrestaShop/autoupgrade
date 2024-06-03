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
if (PHP_SAPI !== 'cli') {
    echo 'This script must be called from CLI';
    exit(1);
}

$inputConfigurationFile = getopt('', ['from::'])['from'];
if (!file_exists($inputConfigurationFile)) {
    echo sprintf('Invalid input configuation file %s', $inputConfigurationFile) . PHP_EOL;
    exit(1);
}

$inputData = json_decode(file_get_contents($inputConfigurationFile), true);

require_once realpath(dirname(__FILE__) . '/../../modules/autoupgrade') . '/ajax-upgradetabconfig.php';
$container = autoupgrade_init_container(dirname(__FILE__));

$container->setLogger(new \PrestaShop\Module\AutoUpgrade\Log\StreamedLogger());
$controller = new \PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\UpdateConfig($container);
$controller->inputCliParameters($inputData);
$controller->init();
exit($controller->run());
