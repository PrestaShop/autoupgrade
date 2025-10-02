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

namespace PrestaShop\Module\AutoUpgrade;

use Exception;
use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Backup\BackupManager;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Log\WebLogger;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\LanguageConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\LocalChannelConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Progress\CompletionCalculator;
use PrestaShop\Module\AutoUpgrade\Router\UrlGenerator;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\DownloadService;
use PrestaShop\Module\AutoUpgrade\Services\LocalVersionFilesService;
use PrestaShop\Module\AutoUpgrade\Services\LogsService;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;
use PrestaShop\Module\AutoUpgrade\Services\UpdateNotificationService;
use PrestaShop\Module\AutoUpgrade\State\AbstractState;
use PrestaShop\Module\AutoUpgrade\State\BackupState;
use PrestaShop\Module\AutoUpgrade\State\LogsState;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\AssetsEnvironment;
use PrestaShop\Module\AutoUpgrade\Twig\TransFilterExtension;
use PrestaShop\Module\AutoUpgrade\Twig\TransFilterExtension3;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CacheCleaner;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreConsoleExecutable;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\AbstractModuleSourceProvider;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\ComposerSourceProvider;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\LocalSourceProvider;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\MarketplaceSourceProvider;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class responsible of the easy (& Lazy) loading of the different services
 * available for the upgrade.
 */
class UpgradeContainer
{
    const WORKSPACE_PATH = 'workspace';
    const BACKUP_PATH = 'backup';
    const DOWNLOAD_PATH = 'download';
    const LOGS_PATH = 'logs';

    // temporary folders
    const TMP_PATH = 'tmp';
    const TMP_FILES_PATH = 'files';
    const TMP_FILES_DIR = 'files/';
    const TMP_MODULES_PATH = 'modules';
    const TMP_MODULES_DIR = 'modules/';
    const TMP_RELEASES_PATH = 'releases';
    const TMP_RELEASES_DIR = 'releases/';

    const PS_ADMIN_PATH = 'ps_admin';
    const PS_ADMIN_SUBDIR = 'ps_admin_subdir';
    const PS_ROOT_PATH = 'ps_root';
    const ARCHIVE_FILENAME = 'destDownloadFilename';
    const ARCHIVE_FILEPATH = 'destDownloadFilepath';
    const PS_VERSION = 'version';
    const ANONYMOUS_USER_ID = 'anonymous_user_id';

    /** @var Analytics */
    private $analytics;

    /** @var BackupFinder */
    private $backupFinder;

    /** @var BackupManager */
    private $backupManager;

    /** @var CacheCleaner */
    private $cacheCleaner;

    /** @var ChecksumCompare */
    private $checksumCompare;

    /** @var ComposerService */
    private $composerService;

    /** @var Cookie */
    private $cookie;

    /** @var \Db */
    public $db;

    /** @var FileStorage */
    private $fileStorage;

    /** @var FileFilter */
    private $fileFilter;

    /** @var PrestashopConfiguration */
    private $prestashopConfiguration;

    /** @var ConfigurationStorage */
    private $configurationStorage;

    /** @var UpgradeConfiguration */
    private $updateConfiguration;

    /** @var RestoreConfiguration */
    private $restoreConfiguration;

    /** @var LanguageConfiguration */
    private $languageConfiguration;

    /** @var FilesystemAdapter */
    private $filesystemAdapter;

    /** @var FileLoader */
    private $fileLoader;

    /** @var Logger */
    private $logger;

    /** @var LogsService */
    private $logsService;

    /** @var ModuleAdapter */
    private $moduleAdapter;

    /** @var AbstractModuleSourceProvider[] */
    private $moduleSourceProviders;

    /** @var CompletionCalculator */
    private $completionCalculator;

    /** @var Twig_Environment|\Twig\Environment */
    private $twig;

    /** @var BackupState */
    private $backupState;

    /** @var LogsState */
    private $logsState;

    /** @var RestoreState */
    private $restoreState;

    /** @var UpdateState */
    private $updateState;

    /** @var SymfonyAdapter */
    private $symfonyAdapter;

    /** @var Upgrader */
    private $upgrader;

    /** @var Workspace */
    private $workspace;

    /** @var ZipAction */
    private $zipAction;

    /** @var AssetsEnvironment */
    private $assetsEnvironment;

    /** @var ConfigurationValidator */
    private $configurationValidator;

    /** @var CoreConsoleExecutable */
    private $coreConsoleExecutable;

    /** @var LocalChannelConfigurationValidator */
    private $localChannelConfigurationValidator;

    /**
     * @var RestoreConfigurationValidator
     */
    private $restoreConfigurationValidator;

    /** @var PrestashopVersionService */
    private $prestashopVersionService;

    /** @var UpgradeSelfCheck */
    private $upgradeSelfCheck;

    /** @var UpdateNotificationService */
    private $updateNotificationService;

    /** @var PhpVersionResolverService */
    private $phpVersionResolverService;

    /** @var DistributionApiService */
    private $distributionApiService;

    /** @var LocalVersionFilesService */
    private $localVersionFilesService;

    /** @var Filesystem */
    private $filesystem;

    /** @var Translator */
    private $translator;

    /** @var Translation */
    private $translation;

    /** @var DownloadService */
    private $downloadService;

    /** @var UrlGenerator */
    private $urlGenerator;

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

    public function __construct(string $psRootDir, string $adminDir, string $moduleSubDir = 'autoupgrade')
    {
        $this->autoupgradeWorkDir = $adminDir . DIRECTORY_SEPARATOR . $moduleSubDir;
        $this->adminDir = $adminDir;
        $this->psRootDir = $psRootDir;

        if ($this->getFileSystem()->exists($psRootDir . '/modules/autoupgrade/.env')) {
            $dotenv = new Dotenv();
            $dotenv->load($psRootDir . '/modules/autoupgrade/.env');
        }
    }

    /**
     * @throws Exception
     */
    public function getProperty(string $property): ?string
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
            case self::TMP_PATH:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'tmp';
            case self::TMP_MODULES_PATH:
                return $this->getProperty(self::TMP_PATH) . DIRECTORY_SEPARATOR . 'modules';
            case self::TMP_MODULES_DIR:
                return $this->getProperty(self::TMP_MODULES_PATH) . DIRECTORY_SEPARATOR;
            case self::TMP_RELEASES_PATH:
                return $this->getProperty(self::TMP_PATH) . DIRECTORY_SEPARATOR . 'releases';
            case self::TMP_RELEASES_DIR:
                return $this->getProperty(self::TMP_RELEASES_PATH) . DIRECTORY_SEPARATOR;
            case self::TMP_FILES_PATH:
                return $this->getProperty(self::TMP_PATH) . DIRECTORY_SEPARATOR . 'files';
            case self::TMP_FILES_DIR:
                return $this->getProperty(self::TMP_FILES_PATH) . DIRECTORY_SEPARATOR;
            case self::LOGS_PATH:
                return $this->autoupgradeWorkDir . DIRECTORY_SEPARATOR . 'logs';
            case self::ARCHIVE_FILENAME:
                return $this->getUpdateConfiguration()->getChannelZip();
            case self::ARCHIVE_FILEPATH:
                return $this->getArchiveFilePath();
            case self::PS_VERSION:
                return $this->getCurrentPrestaShopVersion();
            case self::ANONYMOUS_USER_ID:
                return hash('sha256', $this->getProperty(self::WORKSPACE_PATH));
            default:
                return '';
        }
    }

    /**
     * @throws Exception
     */
    public function getArchiveFilePath(): ?string
    {
        $fileName = $this->getProperty(self::ARCHIVE_FILENAME);

        if ($this->getUpdateConfiguration()->isChannelLocal()) {
            return $this->getProperty(self::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $fileName;
        }

        return $this->getProperty(self::TMP_RELEASES_DIR) . $fileName;
    }

    public function getAnalytics(): Analytics
    {
        if (null === $this->analytics) {
            // The identifier shoudl be a value a value always different between two shops
            // But equal between two upgrade processes
            $this->analytics = new Analytics(
                $this->getUpdateConfiguration(),
                [
                    'update' => $this->getUpdateState(),
                    'restore' => $this->getRestoreState(),
                ],
                $this->getProperty(self::ANONYMOUS_USER_ID), [
                'properties' => [
                    Analytics::WITH_COMMON_PROPERTIES => [
                        'ps_version' => $this->getProperty(self::PS_VERSION),
                        'php_version' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
                        'autoupgrade_version' => $this->getPrestaShopConfiguration()->getModuleVersion(),
                        'php_context' => php_sapi_name() === 'cli' ? 'cli' : 'web',
                    ],
                    Analytics::WITH_UPDATE_PROPERTIES => [
                        'disable_all_overrides' => class_exists('\Configuration', false) ? UpgradeConfiguration::isOverrideAllowed() : null,
                        'regenerate_rtl_stylesheet' => class_exists('\Language', false) ? $this->shouldUpdateRTLFiles() : null,
                    ],
                ],
            ]);
        }

        return $this->analytics;
    }

    public function getBackupFinder(): BackupFinder
    {
        if (null === $this->backupFinder) {
            $this->backupFinder = new BackupFinder($this->getTranslator(), $this->getProperty(self::BACKUP_PATH));
        }

        return $this->backupFinder;
    }

    public function getBackupManager(): BackupManager
    {
        if (null === $this->backupManager) {
            $this->backupManager = new BackupManager($this->getTranslator(), $this->getBackupFinder(), $this->getAnalytics());
        }

        return $this->backupManager;
    }

    /**
     * Init and return CacheCleaner
     */
    public function getCacheCleaner(): CacheCleaner
    {
        if (null === $this->cacheCleaner) {
            $this->cacheCleaner = new CacheCleaner($this, $this->getLogger());
        }

        return $this->cacheCleaner;
    }

    public function getChecksumCompare(): ChecksumCompare
    {
        if (null === $this->checksumCompare) {
            $this->checksumCompare = new ChecksumCompare(
                $this->getFileLoader(),
                $this->getFilesystemAdapter(),
                $this->getProperty(self::PS_ROOT_PATH),
                $this->getProperty(self::PS_ADMIN_PATH)
            );
        }

        return $this->checksumCompare;
    }

    public function getCurrentPrestaShopVersion(): string
    {
        if ($this->getUpdateState()->isInitialized()) {
            return $this->getUpdateState()->getCurrentVersion();
        }

        return $this->getPrestaShopConfiguration()->getPrestaShopVersion();
    }

    public function getComposerService(): ComposerService
    {
        if (null === $this->composerService) {
            $this->composerService = new ComposerService($this->getFileSystem());
        }

        return $this->composerService;
    }

    /**
     * @throws Exception
     */
    public function getCookie(): Cookie
    {
        if (null === $this->cookie) {
            $this->cookie = new Cookie(
                $this->getProperty(self::PS_ADMIN_SUBDIR),
                $this->getProperty(self::TMP_PATH));
        }

        return $this->cookie;
    }

    public function getDb(): \Db
    {
        return \Db::getInstance();
    }

    public function getFileStorage(): FileStorage
    {
        if (null === $this->fileStorage) {
            $this->fileStorage = new FileStorage($this->getFileSystem(), $this->getProperty(self::WORKSPACE_PATH) . DIRECTORY_SEPARATOR);
        }

        return $this->fileStorage;
    }

    /**
     * @throws Exception
     */
    public function getFileFilter(): FileFilter
    {
        if (null === $this->fileFilter) {
            $this->fileFilter = new FileFilter(
                $this->getUpdateConfiguration(),
                $this->getComposerService(),
                $this->getProperty(self::PS_ROOT_PATH)
            );
        }

        return $this->fileFilter;
    }

    /**
     * @throws Exception
     */
    public function getUpgrader(): Upgrader
    {
        if (null === $this->upgrader) {
            if (!defined('_PS_ROOT_DIR_')) {
                define('_PS_ROOT_DIR_', $this->getProperty(self::PS_ROOT_PATH));
            }

            $upgrader = new Upgrader(
                $this->getTranslator(),
                $this->getPhpVersionResolverService(),
                $this->getUpdateConfiguration(),
                $this->getFileSystem(),
                $this->getFileLoader(),
                $this->getDistributionApiService(),
                $this->getProperty(self::PS_VERSION)
            );

            $this->upgrader = $upgrader;
        }

        return $this->upgrader;
    }

    /**
     * @throws Exception
     */
    public function getFilesystemAdapter(): FilesystemAdapter
    {
        if (null === $this->filesystemAdapter) {
            $this->filesystemAdapter = new FilesystemAdapter(
                $this->getFileSystem(),
                $this->getFileFilter(),
                $this->getProperty(self::WORKSPACE_PATH),
                str_replace(
                    $this->getProperty(self::PS_ROOT_PATH),
                    '',
                    $this->getProperty(self::PS_ADMIN_PATH)
                ),
                $this->getProperty(self::PS_ROOT_PATH)
            );
        }

        return $this->filesystemAdapter;
    }

    /**
     * @throws Exception
     */
    public function getFileLoader(): FileLoader
    {
        if (null === $this->fileLoader) {
            $this->fileLoader = new FileLoader($this->getFileSystem(), $this->getDistributionApiService());
        }

        return $this->fileLoader;
    }

    /**
     * @return Logger
     *
     * @throws Exception
     */
    public function getLogger(): Logger
    {
        if (null === $this->logger) {
            $this->logger = (new WebLogger())
                ->setSensitiveData([
                    $this->getProperty(self::PS_ADMIN_SUBDIR) => '**admin_folder**',
                ]);
        }

        return $this->logger;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function getLogsService(): LogsService
    {
        if (null === $this->logsService) {
            $this->logsService = new LogsService(
                $this->getLogsState(),
                $this->getTranslator(),
                $this->getProperty(self::LOGS_PATH)
            );
        }

        return $this->logsService;
    }

    /**
     * @throws Exception
     */
    public function getModuleAdapter(): ModuleAdapter
    {
        if (null === $this->moduleAdapter) {
            $this->moduleAdapter = new ModuleAdapter(
                $this->getTranslator(),
                $this->getProperty(self::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
                $this->getSymfonyAdapter()
            );
        }

        return $this->moduleAdapter;
    }

    /** @return AbstractModuleSourceProvider[] */
    public function getModuleSourceProviders(): array
    {
        if (null === $this->moduleSourceProviders) {
            $this->moduleSourceProviders = [
                new LocalSourceProvider($this->getProperty(self::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . 'modules', $this->getFileStorage()),
                new MarketplaceSourceProvider($this->getUpdateState()->getDestinationVersion(), $this->getProperty(self::PS_ROOT_PATH), $this->getFileLoader(), $this->getFileStorage()),
                new ComposerSourceProvider($this->getProperty(self::TMP_FILES_PATH), $this->getComposerService(), $this->getFileStorage()),
                // Other providers
            ];
        }

        return $this->moduleSourceProviders;
    }

    public function getCompletionCalculator(): CompletionCalculator
    {
        if (null === $this->completionCalculator) {
            $this->completionCalculator = new CompletionCalculator();
        }

        return $this->completionCalculator;
    }

    public function getBackupState(): BackupState
    {
        if (null === $this->backupState) {
            $this->backupState = new BackupState($this->getFileStorage());
            $this->backupState->load();
        }

        return $this->backupState;
    }

    public function getLogsState(): LogsState
    {
        if (null === $this->logsState) {
            $this->logsState = new LogsState($this->getFileStorage());
            $this->logsState->load();
        }

        return $this->logsState;
    }

    public function getRestoreState(): RestoreState
    {
        if (null === $this->restoreState) {
            $this->restoreState = new RestoreState($this->getFileStorage());
            $this->restoreState->load();
        }

        return $this->restoreState;
    }

    public function getUpdateState(): UpdateState
    {
        if (null === $this->updateState) {
            $this->updateState = new UpdateState($this->getFileStorage());
            $this->updateState->load();
        }

        return $this->updateState;
    }

    /**
     * @param TaskType::TASK_TYPE_* $taskType
     *
     * @throws InvalidArgumentException
     */
    public function getStateFromTaskType($taskType): AbstractState
    {
        switch ($taskType) {
            case TaskType::TASK_TYPE_BACKUP:
                return $this->getBackupState();
            case TaskType::TASK_TYPE_RESTORE:
                return $this->getRestoreState();
            case TaskType::TASK_TYPE_UPDATE:
                return $this->getUpdateState();
            default:
                throw new InvalidArgumentException('Unknown task type "' . $taskType . '"');
        }
    }

    /**
     * @throws Exception
     */
    public function getTranslationAdapter(): Translation
    {
        if (null === $this->translation) {
            $this->translation = new Translation($this->getTranslator(), $this->getFileSystem(), $this->getLogger(), $this->getUpdateState()->getInstalledLanguagesIso());
        }

        return $this->translation;
    }

    /**
     * @throws Exception
     */
    public function getTranslator(): Translator
    {
        if (null === $this->translator) {
            $locale = null;
            $languageConfiguration = $this->getLanguageConfiguration();
            // @phpstan-ignore booleanAnd.rightAlwaysTrue, function.alreadyNarrowedType (If PrestaShop core is not instantiated properly, do not try to translate)
            if (method_exists('\Context', 'getContext') && \Context::getContext()->language) {
                $newConfiguration = [
                    LanguageConfiguration::ISO_LANGUAGES => \Context::getContext()->language->iso_code,
                ];

                $languageConfiguration->merge($newConfiguration);
                $this->configurationStorage->save($languageConfiguration);
            }

            $locale = $languageConfiguration->getIsoLanguages();

            $this->translator = new Translator(
                $this->getProperty(self::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'autoupgrade' . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR,
                $locale
            );
        }

        return $this->translator;
    }

    /**
     * @throws LoaderError
     *
     * @return Twig_Environment|Environment
     */
    public function getTwig()
    {
        if (null === $this->twig) {
            if (version_compare($this->getProperty(self::PS_VERSION), '1.7.8.0', '>')) {
                // We use Twig 3
                $loader = new FilesystemLoader();
                $loader->addPath(realpath(__DIR__ . '/..') . '/views/templates', 'ModuleAutoUpgrade');
                $twig = new Environment($loader);
                $twig->addExtension(new TransFilterExtension3($this->getTranslator()));
            } else {
                // We use Twig 1
                // Using independant template engine for 1.6 & 1.7 compatibility
                $loader = new Twig_Loader_Filesystem();
                $loader->addPath(realpath(__DIR__ . '/..') . '/views/templates', 'ModuleAutoUpgrade');
                $twig = new Twig_Environment($loader);
                $twig->addExtension(new TransFilterExtension($this->getTranslator()));
            }

            $this->twig = $twig;
        }

        return $this->twig;
    }

    /**
     * @throws Exception
     */
    public function getPrestaShopConfiguration(): PrestashopConfiguration
    {
        if (null === $this->prestashopConfiguration) {
            $this->prestashopConfiguration = new PrestashopConfiguration(
                $this->getFileSystem(),
                $this->getProperty(self::PS_ROOT_PATH)
            );
        }

        return $this->prestashopConfiguration;
    }

    public function getSymfonyAdapter(): SymfonyAdapter
    {
        if (null === $this->symfonyAdapter) {
            $this->symfonyAdapter = new SymfonyAdapter();
        }

        return $this->symfonyAdapter;
    }

    /**
     * @throws Exception
     */
    public function getConfigurationStorage(): ConfigurationStorage
    {
        if (null === $this->configurationStorage) {
            $this->configurationStorage = new ConfigurationStorage($this->getFileStorage());
        }

        return $this->configurationStorage;
    }

    /**
     * @throws Exception
     */
    public function getUpdateConfiguration(): UpgradeConfiguration
    {
        if (null === $this->updateConfiguration) {
            $this->updateConfiguration = $this->getConfigurationStorage()->loadUpdateConfiguration();
        }

        return $this->updateConfiguration;
    }

    /**
     * @throws Exception
     */
    public function getRestoreConfiguration(): RestoreConfiguration
    {
        if (null === $this->restoreConfiguration) {
            $this->restoreConfiguration = $this->getConfigurationStorage()->loadRestoreConfiguration();
        }

        return $this->restoreConfiguration;
    }

    public function getUpdateNotificationService(): UpdateNotificationService
    {
        if (null === $this->updateNotificationService) {
            $this->updateNotificationService = new UpdateNotificationService();
        }

        return $this->updateNotificationService;
    }

    public function getLanguageConfiguration(): LanguageConfiguration
    {
        if (null === $this->languageConfiguration) {
            $this->languageConfiguration = $this->getConfigurationStorage()->loadLanguageConfiguration();
        }

        return $this->languageConfiguration;
    }

    /**
     * @throws Exception
     */
    public function getDistributionApiService(): DistributionApiService
    {
        if (null === $this->distributionApiService) {
            $this->distributionApiService = new DistributionApiService($this->getTranslator());
        }

        return $this->distributionApiService;
    }

    /**
     * @throws Exception
     */
    public function getPhpVersionResolverService(): PhpVersionResolverService
    {
        if (null === $this->phpVersionResolverService) {
            $this->phpVersionResolverService = new PhpVersionResolverService(
                $this->getDistributionApiService(),
                $this->getProperty(self::PS_VERSION)
            );
        }

        return $this->phpVersionResolverService;
    }

    /**
     * @throws Exception
     */
    public function getUpgradeSelfCheck(): UpgradeSelfCheck
    {
        if (null === $this->upgradeSelfCheck) {
            $this->initPrestaShopCore();

            $this->upgradeSelfCheck = new UpgradeSelfCheck(
                $this->getUpgrader(),
                $this->getUpdateConfiguration(),
                $this->getPrestaShopConfiguration(),
                $this->getTranslator(),
                $this->getPhpVersionResolverService(),
                $this->getChecksumCompare(),
                $this->psRootDir,
                $this->adminDir,
                $this->getProperty(UpgradeContainer::WORKSPACE_PATH),
                $this->getProperty(UpgradeContainer::PS_VERSION)
            );
        }

        return $this->upgradeSelfCheck;
    }

    /**
     * @throws Exception
     */
    public function getWorkspace(): Workspace
    {
        if (null === $this->workspace) {
            $paths = [];
            $properties = [
                self::WORKSPACE_PATH,
                self::BACKUP_PATH,
                self::DOWNLOAD_PATH,
                self::TMP_FILES_PATH,
                self::LOGS_PATH,
                self::TMP_PATH,
                self::TMP_MODULES_DIR,
                self::TMP_RELEASES_DIR,
            ];

            foreach ($properties as $property) {
                $paths[] = $this->getProperty($property);
            }

            $this->workspace = new Workspace(
                $this->getTranslator(),
                $this->getFileSystem(),
                $paths
            );
        }

        return $this->workspace;
    }

    /**
     * @throws Exception
     */
    public function getZipAction(): ZipAction
    {
        if (null === $this->zipAction) {
            $this->zipAction = new ZipAction(
                $this->getFileSystem(),
                $this->getTranslator(),
                $this->getLogger(),
                $this->getUpdateConfiguration(),
                $this->getProperty(self::PS_ROOT_PATH));
        }

        return $this->zipAction;
    }

    /**
     * @return AssetsEnvironment
     *
     * @throws Exception
     */
    public function getAssetsEnvironment(): AssetsEnvironment
    {
        if (null === $this->assetsEnvironment) {
            $this->assetsEnvironment = new AssetsEnvironment($this->getUrlGenerator());
        }

        return $this->assetsEnvironment;
    }

    /**
     * @return ConfigurationValidator
     */
    public function getConfigurationValidator(): ConfigurationValidator
    {
        if (null === $this->configurationValidator) {
            $this->configurationValidator = new ConfigurationValidator(
                $this->getTranslator()
            );
        }

        return $this->configurationValidator;
    }

    public function getCoreConsoleExecutable(): CoreConsoleExecutable
    {
        if (null === $this->coreConsoleExecutable) {
            $this->coreConsoleExecutable = new CoreConsoleExecutable(
                $this->getProperty(self::PS_ROOT_PATH)
            );
        }

        return $this->coreConsoleExecutable;
    }

    /**
     * @return LocalChannelConfigurationValidator
     */
    public function getLocalChannelConfigurationValidator(): LocalChannelConfigurationValidator
    {
        if (null === $this->localChannelConfigurationValidator) {
            $this->localChannelConfigurationValidator = new LocalChannelConfigurationValidator(
                $this->getTranslator(),
                $this->getPrestashopVersionService(),
                $this->getProperty(self::DOWNLOAD_PATH)
            );
        }

        return $this->localChannelConfigurationValidator;
    }

    public function getRestoreConfigurationValidator(): RestoreConfigurationValidator
    {
        if (null === $this->restoreConfigurationValidator) {
            $this->restoreConfigurationValidator = new RestoreConfigurationValidator(
                $this->getTranslator(),
                $this->getBackupFinder()
            );
        }

        return $this->restoreConfigurationValidator;
    }

    /**
     * @return PrestashopVersionService
     */
    public function getPrestashopVersionService(): PrestashopVersionService
    {
        if (null === $this->prestashopVersionService) {
            $this->prestashopVersionService = new PrestashopVersionService($this->getZipAction(), $this->getFileSystem());
        }

        return $this->prestashopVersionService;
    }

    public function getFileSystem(): Filesystem
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    /**
     * @return DownloadService
     */
    public function getDownloadService(): DownloadService
    {
        if (null !== $this->downloadService) {
            return $this->downloadService;
        }

        return $this->downloadService = new DownloadService($this->getTranslator(), $this->getLogger());
    }

    /**
     * @return LocalVersionFilesService
     */
    public function getLocalVersionFilesService(): LocalVersionFilesService
    {
        if (null === $this->localVersionFilesService) {
            $this->localVersionFilesService = new LocalVersionFilesService($this->getPrestashopVersionService(), $this->getProperty(UpgradeContainer::DOWNLOAD_PATH), $this->getProperty(UpgradeContainer::PS_VERSION));
        }

        return $this->localVersionFilesService;
    }

    /**
     * @return UrlGenerator
     */
    public function getUrlGenerator(): UrlGenerator
    {
        if (null === $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator(
                $this->getProperty(self::PS_ADMIN_SUBDIR)
            );
        }

        return $this->urlGenerator;
    }

    /**
     * Loads classes that should normally have collisions with Prestashop dependencies.
     */
    public function loadNecessaryClasses(): void
    {
        // PrestaShop v9.0.0
        if (php_sapi_name() === 'cli') {
            class_exists(Table::class);
            class_exists(TableStyle::class);
            class_exists(Helper::class);
        } else {
            class_exists(ResponseHeaderBag::class);
        }
    }

    /**
     * Checks if the composer autoload exists, and loads it.
     *
     * @throws Exception
     */
    public function initPrestaShopAutoloader(): void
    {
        $autoloader = $this->getProperty(self::PS_ROOT_PATH) . '/vendor/autoload.php';
        if ($this->getFileSystem()->exists($autoloader)) {
            require_once $autoloader;
        }

        require_once $this->getProperty(self::PS_ROOT_PATH) . '/config/defines.inc.php';
        require_once $this->getProperty(self::PS_ROOT_PATH) . '/config/autoload.php';
    }

    /**
     * @throws Exception
     */
    public function initPrestaShopCore(): void
    {
        require_once $this->getProperty(self::PS_ROOT_PATH) . '/config/config.inc.php';

        $id_employee = !empty($_COOKIE['id_employee']) ? $_COOKIE['id_employee'] : 1;
        \Context::getContext()->employee = new \Employee((int) $id_employee);

        // During a CLI process, we reset the original time zone, which was modified with the call to CORE
        if (php_sapi_name() === 'cli') {
            date_default_timezone_set($this->getLogsState()->getTimeZone());
        }
    }

    /**
     * Attemps to flush opcache
     */
    public function resetOpcache(): void
    {
        $disabled = explode(',', ini_get('disable_functions'));

        // @phpstan-ignore function.alreadyNarrowedType (Actually depends on the user configuration)
        if (in_array('opcache_reset', $disabled) || !is_callable('opcache_reset')) {
            return;
        }

        opcache_reset();
    }

    /**
     * @return bool True if we should update RTL files
     */
    public function shouldUpdateRTLFiles(): bool
    {
        $languages = \Language::getLanguages(false);

        foreach ($languages as $lang) {
            if ($lang['is_rtl']) {
                return true;
            }
        }

        return false;
    }
}
