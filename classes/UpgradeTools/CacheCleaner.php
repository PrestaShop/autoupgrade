<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Exception;
use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class CacheCleaner
{
    /**
     * @var UpgradeContainer
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(UpgradeContainer $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @throws Exception
     */
    public function cleanFolders(): void
    {
        $dirsToClean = [
            $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/app/cache/',
            $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/cache/smarty/cache/',
            $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/cache/smarty/compile/',
            $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/var/cache/',
        ];

        $defaultThemeNames = [
            'default',
            'prestashop',
            'default-boostrap',
            'classic',
        ];

        if (defined('_THEME_NAME_') && in_array(_THEME_NAME_, $defaultThemeNames)) {
            $dirsToClean[] = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/themes/' . _THEME_NAME_ . '/cache/';
        }

        foreach ($dirsToClean as $dir) {
            if (!$this->container->getFileSystem()->exists($dir)) {
                $this->logger->debug($this->container->getTranslator()->trans('Directory "%s" does not exist and cannot be emptied.', [str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $dir)]));
                continue;
            }
            $this->container->getFilesystemAdapter()->clearDirectory($dir);
            $this->logger->debug($this->container->getTranslator()->trans('Directory %s emptied', [$dir]));
        }
    }
}
