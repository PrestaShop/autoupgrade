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

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use PrestaShop\Module\AutoUpgrade\ErrorHandler;
use PrestaShop\Module\AutoUpgrade\Log\CliLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Log\StreamedLogger;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var UpgradeContainer
     */
    protected $upgradeContainer;

    /**
     * @throws Exception
     */
    protected function setupContainer(InputInterface $input, OutputInterface $output): void
    {
        $this->logger = $output->isDecorated() ? new CliLogger($output) : new StreamedLogger();
        if ($output->isQuiet()) {
            $this->logger->setFilter(Logger::ERROR);
        } elseif ($output->isVerbose()) {
            $this->logger->setFilter(Logger::DEBUG);
        } else {
            $this->logger->setFilter(Logger::INFO);
        }

        $prodRootDir = _PS_ROOT_DIR_;
        $this->logger->debug('Production root directory: ' . $prodRootDir);

        $adminDir = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $input->getArgument('admin-dir');
        $this->logger->debug('Admin directory: ' . $adminDir);
        define('_PS_ADMIN_DIR_', $adminDir);

        $this->upgradeContainer = new UpgradeContainer($prodRootDir, $adminDir);
        $this->logger->debug('Upgrade container initialized.');

        $this->logger->debug('Logger initialized: ' . get_class($this->logger));

        $this->upgradeContainer->setLogger($this->logger);
        (new ErrorHandler($this->logger))->enable();
        $this->logger->debug('Error handler enabled.');
    }
}
