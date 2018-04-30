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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\State;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\PrestashopConfiguration;
use PrestaShop\Module\AutoUpgrade\ZipAction;
use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Twig\TransFilterExtension;

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
    const LATEST_DIR = 'lastest/';
    const TMP_PATH = 'tmp';
    const PS_ADMIN_PATH = 'ps_admin';
    const PS_ADMIN_SUBDIR = 'ps_admin_subdir';
    const PS_ROOT_PATH = 'ps_root'; // AdminSelfUpgrade::$prodRootDir
    const ARCHIVE_FILENAME = 'destDownloadFilename';
    const ARCHIVE_FILEPATH = 'destDownloadFilepath';
    const PS_VERSION = 'version';

    /**
     * @var Db
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ModuleAdapter
     */
    private $moduleAdapter;

    /**
     * @var Twig_Environment
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
     * @var ZipAction
     */
    private $zipAction;

    /**
     * AdminSelfUpgrade::$autoupgradePath
     * Ex.: /var/www/html/PrestaShop/admin-dev/autoupgrade
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

    /**
     * @var string Destination filename of the downloaded archive
     */
    private $destDownloadFilename = 'prestashop.zip';

    public function __construct($psRootDir, $adminDir, $moduleSubDir = 'autoupgrade')
    {
        $this->autoupgradeWorkDir = $adminDir.DIRECTORY_SEPARATOR.$moduleSubDir;
        $this->adminDir = $adminDir;
        $this->psRootDir = $psRootDir;
    }

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
                return $this->autoupgradeWorkDir.DIRECTORY_SEPARATOR.'backup';
            case self::DOWNLOAD_PATH:
                return $this->autoupgradeWorkDir.DIRECTORY_SEPARATOR.'download';
            case self::LATEST_PATH:
                return $this->autoupgradeWorkDir.DIRECTORY_SEPARATOR.'latest';
            case self::LATEST_DIR:
                return $this->autoupgradeWorkDir.DIRECTORY_SEPARATOR.'latest'.DIRECTORY_SEPARATOR;
            case self::TMP_PATH:
                return $this->autoupgradeWorkDir.DIRECTORY_SEPARATOR.'tmp';
            case self::ARCHIVE_FILENAME:
                return $this->destDownloadFilename;
            case self::ARCHIVE_FILEPATH:
                return $this->getProperty(self::DOWNLOAD_PATH).DIRECTORY_SEPARATOR.$this->destDownloadFilename;
            case self::PS_VERSION:
                return $this->getPrestaShopConfiguration()->getPrestaShopVersion();
        }
    }

    public function getDb()
    {
        return \Db::getInstance();
    }

    /**
     * Return the path to the zipfile containing prestashop.
     * @return type
     */
    public function getFilePath()
    {
        return $this->getProperty(self::ARCHIVE_FILEPATH);
    }

    public function getFileConfigurationStorage()
    {
        if (!is_null($this->fileConfigurationStorage)) {
            return $this->fileConfigurationStorage;
        }

        $this->fileConfigurationStorage = new FileConfigurationStorage($this->getProperty(self::WORKSPACE_PATH).DIRECTORY_SEPARATOR);
        return $this->fileConfigurationStorage;
    }

    public function getFileFilter()
    {
        if (!is_null($this->fileFilter)) {
            return $this->fileFilter;
        }

        $this->fileFilter = new FileFilter($this->getUpgradeConfiguration());
        return $this->fileFilter;
    }

    public function getUpgrader()
    {
        if (!is_null($this->upgrader)) {
            return $this->upgrader;
        }
        if (!defined('_PS_ROOT_DIR_')) {
            define('_PS_ROOT_DIR_', $this->getProperty(self::PS_ROOT_PATH));
        }
        // in order to not use Tools class
        $upgrader = new Upgrader($this->getProperty(self::PS_VERSION));
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', $this->getProperty(self::PS_VERSION), $matches);
        $upgrader->branch = $matches[1];
        $upgradeConfiguration = $this->getUpgradeConfiguration();
        $channel = $upgradeConfiguration->get('channel');
        switch ($channel) {
            case 'archive':
                $upgrader->channel = 'archive';
                $upgrader->version_num = $upgradeConfiguration->get('archive.version_num');
                $this->destDownloadFilename = $upgradeConfiguration->get('archive.filename');
                $upgrader->checkPSVersion(true, array('archive'));
                break;
            case 'directory':
                $upgrader->channel = 'directory';
                $upgrader->version_num = $upgradeConfiguration->get('directory.version_num');
                $upgrader->checkPSVersion(true, array('directory'));
                break;
            default:
                $upgrader->channel = $channel;
                if ($upgradeConfiguration->get('channel') == 'private' && !$upgradeConfiguration->get('private_allow_major')) {
                    $upgrader->checkPSVersion(false, array('private', 'minor'));
                } else {
                    $upgrader->checkPSVersion(false, array('minor'));
                }
        }
        $this->getState()->setInstallVersion($upgrader->version_num);
        $this->upgrader = $upgrader;
        return $this->upgrader;
    }

    public function getFilesystemAdapter()
    {
        if (!is_null($this->filesystemAdapter)) {
            return $this->filesystemAdapter;
        }

        $this->filesystemAdapter = new FilesystemAdapter(
            $this->getFileFilter(),
            $this->getState()->getRestoreFilesFilename(),
            $this->getProperty(self::WORKSPACE_PATH),
            str_replace($this->getProperty(self::PS_ROOT_PATH), '', $this->getProperty(self::PS_ADMIN_PATH)), $this->getProperty(self::PS_ROOT_PATH));

        return $this->filesystemAdapter;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (! is_null($this->logger)) {
            return $this->logger;
        }

        $logFile = $this->getProperty(self::TMP_PATH).DIRECTORY_SEPARATOR.'log.txt';
        $this->logger = new LegacyLogger($logFile);
        return $this->logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getModuleAdapter()
    {
        if (!is_null($this->moduleAdapter)) {
            return $this->moduleAdapter;
        }

        $this->moduleAdapter = new ModuleAdapter(
            $this->getDb(),
            $this->getTranslator(),
            $this->getProperty(self::PS_ROOT_PATH).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR,
            $this->getProperty(self::TMP_PATH),
            $this->getState()->getInstallVersion(),
            $this->getZipAction());

        return $this->moduleAdapter;
    }

    public function getState()
    {
        if (!is_null($this->state)) {
            return $this->state;
        }

        $this->state = new State();
        return $this->state;
    }

    public function getTranslationAdapter()
    {
        return new Translation($this->getTranslator(), $this->getLogger(), $this->getState()->getInstalledLanguagesIso());
    }

    public function getTranslator()
    {
        return new \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator('AdminSelfUpgrade');
    }

    public function getTwig()
    {
        if (!is_null($this->twig)) {
            return $this->twig;
        }

        // Using independant template engine for 1.6 & 1.7 compatibility
        $loader = new \Twig_Loader_Filesystem();
        $loader->addPath(realpath(__DIR__.'/..').'/views/templates', 'ModuleAutoUpgrade');
        $twig = new \Twig_Environment($loader, array(
            //'cache' => '/path/to/compilation_cache',
        ));
        $twig->addExtension(new TransFilterExtension($this->getTranslator()));

        $this->twig = $twig;
        return $this->twig;
    }

    public function getPrestaShopConfiguration()
    {
        if (!is_null($this->prestashopConfiguration)) {
            return $this->prestashopConfiguration;
        }

        $this->prestashopConfiguration = new PrestashopConfiguration(
            $this->getProperty(self::WORKSPACE_PATH),
            $this->getProperty(self::PS_ROOT_PATH)
        );
        return $this->prestashopConfiguration;
    }

    public function getSymfonyAdapter()
    {
        if (!is_null($this->symfonyAdapter)) {
            return $this->symfonyAdapter;
        }

        $this->symfonyAdapter = new SymfonyAdapter($this->getState()->getInstallVersion());
        return $this->symfonyAdapter;
    }

    public function getUpgradeConfiguration()
    {
        if (!is_null($this->upgradeConfiguration)) {
            return $this->upgradeConfiguration;
        }
        $upgradeConfigurationStorage = new UpgradeConfigurationStorage($this->getProperty(self::WORKSPACE_PATH).DIRECTORY_SEPARATOR);
        $this->upgradeConfiguration = $upgradeConfigurationStorage->load(UpgradeFileNames::configFilename);
        return $this->upgradeConfiguration;
    }

    public function getZipAction()
    {
        if (!is_null($this->zipAction)) {
            return $this->zipAction;
        }

        $this->zipAction = new ZipAction($this->getTranslator(), $this->getLogger(), $this->getProperty(self::PS_ROOT_PATH));
        return $this->zipAction;
    }

    /**
     * Checks if the composer autoload exists, and loads it.
     */
    public function initPrestaShopAutoloader()
    {
        $autoloader = $this->getProperty(self::PS_ROOT_PATH).'/vendor/autoload.php';
        if (file_exists($autoloader)) {
            require_once($autoloader);
        }

        require_once($this->getProperty(self::PS_ROOT_PATH).'/config/defines.inc.php');
        require_once($this->getProperty(self::PS_ROOT_PATH).'/config/autoload.php');
    }

    public function initPrestaShopCore()
    {
        require_once($this->getProperty(self::PS_ROOT_PATH).'/config/config.inc.php');

        $id_employee = !empty($_COOKIE['id_employee']) ? $_COOKIE['id_employee'] : 1;
        \Context::getContext()->employee = new \Employee((int) $id_employee);
    }
}
