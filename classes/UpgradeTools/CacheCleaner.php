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

        if (defined('_THEME_NAME_') && $this->container->getUpgradeConfiguration()->shouldUpdateDefaultTheme() && in_array(_THEME_NAME_, $defaultThemeNames)) {
            $dirsToClean[] = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/themes/' . _THEME_NAME_ . '/cache/';
        }

        foreach ($dirsToClean as $dir) {
            if (!file_exists($dir)) {
                $this->logger->debug($this->container->getTranslator()->trans('[SKIP] directory "%s" does not exist and cannot be emptied.', [str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $dir)]));
                continue;
            }
            foreach (scandir($dir) as $file) {
                if ($file[0] === '.' || $file === 'index.php') {
                    continue;
                }
                // ToDo: Use Filesystem instead ?
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                } elseif (is_dir($dir . $file . DIRECTORY_SEPARATOR)) {
                    FilesystemAdapter::deleteDirectory($dir . $file . DIRECTORY_SEPARATOR);
                }
                $this->logger->debug($this->container->getTranslator()->trans('[CLEANING CACHE] File %s removed', [$file]));
            }
        }
    }
}
