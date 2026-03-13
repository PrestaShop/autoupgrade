<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
