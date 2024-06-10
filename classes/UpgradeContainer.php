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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Twig\TransFilterExtension;
use PrestaShop\Module\AutoUpgrade\Twig\TransFilterExtension3;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CacheCleaner;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class responsible of the easy (& Lazy) loading of the different services
 * available for the upgrade.
 */
class UpgradeContainer
{
    const WORKSPACE_PATH = 'workspace'; // AdminSelfUpgrade::$autoupgradePath
    const BACKUP_PATH = 'backup';
    const DOWNLOAD_PATH = 'download';
    const LATEST_PATH = 'latest'; // AdminSelfUpgrade::$latestRootDir
    const LATEST_DIR = 'latest/';
    const TMP_PATH = 'tmp';
    const PS_ADMIN_PATH = 'ps_admin';
    const PS_ADMIN_SUBDIR = 'ps_admin_subdir';
    const PS_ROOT_PATH = 'ps_root'; // AdminSelfUpgrade::$prodRootDir
    const ARCHIVE_FILENAME = 'destDownloadFilename';
    const ARCHIVE_FILEPATH = 'destDownloadFilepath';
    const PS_VERSION = 'version';
    const DB_CONFIG_KEYS = ['PS_DISABLE_OVERRIDES'];

    /**
     * @var CacheCleaner
     */
    private $cacheCleaner;

    /**
     * @var ChecksumCompare
     */
    private $checksumCompare;

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @var \Db
     */
    public $db;

    /**
     * @var FileConfigurationStorage
     */
    private $fileConfigurationStorage;

    /**
     * @var FileFilter
     */
    private $fileFilter;

    /**
     * @var PrestashopConfiguration
     */
    private $prestashopConfiguration;

    /**
     * @var UpgradeConfiguration
     */
    private $upgradeConfiguration;

    /**
     * @var FilesystemAdapter
     */
    private $filesystemAdapter;

    /**
     * @var FileLoader
     */
    private $fileLoader;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ModuleAdapter
     */
    private $moduleAdapter;

    /**
     * @var Twig_Environment|\Twig\Environment
     */
    private $twig;

    /**
     * @var State
     */
    private $state;

    /**
     * @var SymfonyAdapter
     */
    private $symfonyAdapter;

    /**
     * @var Upgrader
     */
    private $upgrader;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ZipAction
     */
    private $zipAction;

    /**
     * AdminSelfUpgrade::$autoupgradePath
     * Ex.: /var/www/html/PrestaShop/admin-dev/autoupgrade.
     *
     * @var string Path to the base folder of the autoupgrade (in admin)
     */
    private $autoupgradeWorkDir;

    /**
     * @var string Absolute path to the admin folder
     */
    private $adminDir;

    /**
     * @var string Absolute path to ps root folder of PS
     */
    private $psRootDir;

    public function __construct($psRootDir, $adminDir, $moduleSubDir = 'autoupgrade')
    {
        $this->autoupgradeWorkDir = $adminDir . DIRECTORY_SEPARATOR . $moduleSubDir;
        $this->adminDir = $adminDir;
        $this->psRootDir = $psRootDir;
    }

    /**
     * @return string
     */
    public function getProperty($property)
    {
        switch ($property) {
            case self::PS_ADMIN_PATH:
                return $this->adminDir;
            case self::PS_ADMIN_SUBDIR:
                return trim(str_replace($this->getProperty(self::PS_ROOT_PATH), '', $this->getProperty(self::PS_ADMIN_PATH)), DIRECTORY_SEPARATOR);
            case self::PS_ROOT_PATH:
                return $this->psRootDir;
            case self::WORKSPACE_PATH:
                return $this->autoupgradeWorkDir;
            case self::BACKUP_PATH:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'backup';
            case self::DOWNLOAD_PATH:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'download';
            case self::LATEST_PATH:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'latest';
            case self::LATEST_DIR:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'latest' . DIRECTORY_SEPARATOR;
            case self::TMP_PATH:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'tmp';
            case self::ARCHIVE_FILENAME:
                return $this->getUpgradeConfiguration()->getArchiveFilename();
            case self::ARCHIVE_FILEPATH:
                return $this->getProperty(self::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $this->getProperty(self::ARCHIVE_FILENAME);
            case self::PS_VERSION:
                return $this->getPrestaShopConfiguration()->getPrestaShopVersion();
            default:
                return '';
        }
    }

    /**
     * Init and return CacheCleaner
     *
     * @return CacheCleaner
     */
    public function getCacheCleaner()
    {
        if (null !== $this->cacheCleaner) {
            return $this->cacheCleaner;
        }

        return $this->cacheCleaner = new CacheCleaner($this, $this->getLogger());
    }

    /**
     * @return ChecksumCompare
     */
    public function getChecksumCompare()
    {
        if (null !== $this->checksumCompare) {
            return $this->checksumCompare;
        }

        $this->checksumCompare = new ChecksumCompare(
            $this->getFileLoader(),
            $this->getFilesystemAdapter()
        );

        return $this->checksumCompare;
    }

    /**
     * @return Cookie
     */
    public function getCookie()
    {
        if (null !== $this->cookie) {
            return $this->cookie;
        }

        $this->cookie = new Cookie(
            $this->getProperty(self::PS_ADMIN_SUBDIR),
            $this->getProperty(self::TMP_PATH));

        return $this->cookie;
    }

    /**
     * @return \Db
     */
    public function getDb()
    {
        return \Db::getInstance();
    }

    /**
     * Return the path to the zipfile containing prestashop.
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->getProperty(self::ARCHIVE_FILEPATH);
    }

    /**
     * @return FileConfigurationStorage
     */
    public function getFileConfigurationStorage()
    {
        if (null !== $this->fileConfigurationStorage) {
            return $this->fileConfigurationStorage;
        }

        $this->fileConfigurationStorage = new FileConfigurationStorage($this->getProperty(self::WORKSPACE_PATH) . DIRECTORY_SEPARATOR);

        return $this->fileConfigurationStorage;
    }

    /**
     * @return FileFilter
     */
    public function getFileFilter()
    {
        if (null !== $this->fileFilter) {
            return $this->fileFilter;
        }

        $this->fileFilter = new FileFilter(
            $this->getUpgradeConfiguration(),
            $this->getProperty(self::PS_ROOT_PATH)
        );

        return $this->fileFilter;
    }

    /**
     * @return Upgrader
     */
    public function getUpgrader()
    {
        if (null !== $this->upgrader) {
            return $this->upgrader;
        }
        if (!defined('_PS_ROOT_DIR_')) {
            define('_PS_ROOT_DIR_', $this->getProperty(self::PS_ROOT_PATH));
        }

        $fileLoader = $this->getFileLoader();
        // in order to not use Tools class
        $upgrader = new Upgrader(
            $this->getProperty(self::PS_VERSION),
            $fileLoader
        );
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', $this->getProperty(self::PS_VERSION), $matches);
        $upgrader->branch = $matches[1];
        $upgradeConfiguration = $this->getUpgradeConfiguration();
        $channel = $upgradeConfiguration->get('channel');
        switch ($channel) {
            case 'archive':
                $upgrader->channel = 'archive';
                $upgrader->version_num = $upgradeConfiguration->get('archive.version_num');
                $archiveXml = $upgradeConfiguration->get('archive.xml');
                if (!empty($archiveXml)) {
                    // TODO: Change this wild push to a public variable
                    $fileLoader->version_md5[$upgrader->version_num] = $this->getProperty(self::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $archiveXml;
                }
                $upgrader->checkPSVersion(true, ['archive']);
                break;
            case 'directory':
                $upgrader->channel = 'directory';
                $upgrader->version_num = $upgradeConfiguration->get('directory.version_num');
                $upgrader->checkPSVersion(true, ['directory']);
                break;
            default:
                $upgrader->channel = $channel;
                if ($upgradeConfiguration->get('channel') == 'private' && !$upgradeConfiguration->get('private_allow_major')) {
                    $upgrader->checkPSVersion(false, ['private', 'minor']);
                } else {
                    $upgrader->checkPSVersion(false, ['minor']);
                }
        }
        $this->getState()->setInstallVersion($upgrader->version_num);
        $this->upgrader = $upgrader;

        return $this->upgrader;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getFilesystemAdapter()
    {
        if (null !== $this->filesystemAdapter) {
            return $this->filesystemAdapter;
        }

        $this->filesystemAdapter = new FilesystemAdapter(
            $this->getFileFilter(),
            $this->getState()->getRestoreFilesFilename(),
            $this->getProperty(self::WORKSPACE_PATH),
            str_replace(
                $this->getProperty(self::PS_ROOT_PATH),
                '',
                $this->getProperty(self::PS_ADMIN_PATH)
            ),
            $this->getProperty(self::PS_ROOT_PATH)
        );

        return $this->filesystemAdapter;
    }

    /**
     * @return FileLoader
     */
    public function getFileLoader()
    {
        if (null !== $this->fileLoader) {
            return $this->fileLoader;
        }

        $this->fileLoader = new FileLoader();

        return $this->fileLoader;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (null !== $this->logger) {
            return $this->logger;
        }

        $logFile = null;
        if (is_writable($this->getProperty(self::TMP_PATH))) {
            $logFile = $this->getProperty(self::TMP_PATH) . DIRECTORY_SEPARATOR . 'log.txt';
        }
        $this->logger = new LegacyLogger($logFile);

        return $this->logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return ModuleAdapter
     */
    public function getModuleAdapter()
    {
        if (null !== $this->moduleAdapter) {
            return $this->moduleAdapter;
        }

        $this->moduleAdapter = new ModuleAdapter(
            $this->getTranslator(),
            $this->getProperty(self::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
            $this->getProperty(self::TMP_PATH),
            $this->getState()->getInstallVersion(),
            $this->getZipAction(),
            $this->getSymfonyAdapter()
        );

        return $this->moduleAdapter;
    }

    /**
     * @return State
     */
    public function getState()
    {
        if (null !== $this->state) {
            return $this->state;
        }

        $this->state = new State();

        return $this->state;
    }

    /**
     * @return Translation
     */
    public function getTranslationAdapter()
    {
        return new Translation($this->getTranslator(), $this->getLogger(), $this->getState()->getInstalledLanguagesIso());
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        $translator = $this->getSymfonyAdapter()->initKernel()
        ->getContainer()
        ->get('translator');

        return $translator ?? new Translator();
    }

    /**
     * @return Twig_Environment|\Twig\Environment
     */
    public function getTwig()
    {
        if (null !== $this->twig) {
            return $this->twig;
        }

        if (class_exists(Twig_Environment::class)) {
            // We use Twig 1
            // Using independant template engine for 1.6 & 1.7 compatibility
            $loader = new Twig_Loader_Filesystem();
            $loader->addPath(realpath(__DIR__ . '/..') . '/views/templates', 'ModuleAutoUpgrade');
            $twig = new Twig_Environment($loader);
            $twig->addExtension(new TransFilterExtension($this->getTranslator()));
        } else {
            // We use Twig 3
            $loader = new \Twig\Loader\FilesystemLoader();
            $loader->addPath(realpath(__DIR__ . '/..') . '/views/templates', 'ModuleAutoUpgrade');
            $twig = new \Twig\Environment($loader);
            $twig->addExtension(new TransFilterExtension3($this->getTranslator()));
        }

        $this->twig = $twig;

        return $this->twig;
    }

    /**
     * @return PrestashopConfiguration
     */
    public function getPrestaShopConfiguration()
    {
        if (null !== $this->prestashopConfiguration) {
            return $this->prestashopConfiguration;
        }

        $this->prestashopConfiguration = new PrestashopConfiguration(
            $this->getProperty(self::PS_ROOT_PATH)
        );

        return $this->prestashopConfiguration;
    }

    /**
     * @return SymfonyAdapter
     */
    public function getSymfonyAdapter()
    {
        if (null !== $this->symfonyAdapter) {
            return $this->symfonyAdapter;
        }

        $this->symfonyAdapter = new SymfonyAdapter();

        return $this->symfonyAdapter;
    }

    /**
     * @return UpgradeConfiguration
     */
    public function getUpgradeConfiguration()
    {
        if (null !== $this->upgradeConfiguration) {
            return $this->upgradeConfiguration;
        }
        $upgradeConfigurationStorage = new UpgradeConfigurationStorage($this->getProperty(self::WORKSPACE_PATH) . DIRECTORY_SEPARATOR);
        $this->upgradeConfiguration = $upgradeConfigurationStorage->load(UpgradeFileNames::CONFIG_FILENAME);

        return $this->upgradeConfiguration;
    }

    /**
     * @return UpgradeConfigurationStorage
     */
    public function getUpgradeConfigurationStorage()
    {
        return new UpgradeConfigurationStorage($this->getProperty(self::WORKSPACE_PATH) . DIRECTORY_SEPARATOR);
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        if (null !== $this->workspace) {
            return $this->workspace;
        }

        $paths = [];
        $properties = [
            self::WORKSPACE_PATH, self::BACKUP_PATH,
            self::DOWNLOAD_PATH, self::LATEST_PATH,
            self::TMP_PATH, ];

        foreach ($properties as $property) {
            $paths[] = $this->getProperty($property);
        }

        $this->workspace = new Workspace(
            $this->getLogger(),
            $this->getTranslator(),
            $paths
        );

        return $this->workspace;
    }

    /**
     * @return ZipAction
     */
    public function getZipAction()
    {
        if (null !== $this->zipAction) {
            return $this->zipAction;
        }

        $this->zipAction = new ZipAction(
            $this->getTranslator(),
            $this->getLogger(),
            $this->getUpgradeConfiguration(),
            $this->getProperty(self::PS_ROOT_PATH));

        return $this->zipAction;
    }

    /**
     * Checks if the composer autoload exists, and loads it.
     */
    public function initPrestaShopAutoloader()
    {
        $autoloader = $this->getProperty(self::PS_ROOT_PATH) . '/vendor/autoload.php';
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }

        require_once $this->getProperty(self::PS_ROOT_PATH) . '/config/defines.inc.php';
        require_once $this->getProperty(self::PS_ROOT_PATH) . '/config/autoload.php';
    }

    public function initPrestaShopCore()
    {
        require_once $this->getProperty(self::PS_ROOT_PATH) . '/config/config.inc.php';

        $id_employee = !empty($_COOKIE['id_employee']) ? $_COOKIE['id_employee'] : 1;
        \Context::getContext()->employee = new \Employee((int) $id_employee);
    }

    /**
     * Attemps to flush opcache
     */
    public function resetOpcache()
    {
        $disabled = explode(',', ini_get('disable_functions'));

        if (in_array('opcache_reset', $disabled) || !is_callable('opcache_reset')) {
            return;
        }

        opcache_reset();
    }
}
