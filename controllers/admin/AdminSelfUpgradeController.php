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

use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradePage;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;

class AdminSelfUpgradeController extends ModuleAdminController
{
    /** @var Autoupgrade */
    public $module;
    public $multishop_context_group = false;
    /** @var bool */
    public $ajax = false;
    /** @var bool */
    public $standalone = true;

    /**
     * Initialized in initPath().
     */
    /** @var string */
    public $autoupgradePath;
    /** @var string */
    public $downloadPath;
    /** @var string */
    public $backupPath;
    /** @var string */
    public $latestPath;
    /** @var string */
    public $tmpPath;

    /**
     * autoupgradeDir.
     *
     * @var string directory relative to admin dir
     */
    /** @var string */
    public $autoupgradeDir = 'autoupgrade';
    /** @var string */
    public $prodRootDir = '';
    /** @var string */
    public $adminDir = '';

    /** @var array<string, mixed[]> */
    public $_fieldsUpgradeOptions = [];
    /** @var array<string, mixed[]> */
    public $_fieldsBackupOptions = [];

    /**
     * @var UpgradeContainer
     */
    private $upgradeContainer;

    /**
     * @var Db
     */
    public $db;

    /** @var string[] */
    public $_errors = [];
    /** @var bool */
    private $isActualPHPVersionCompatible = true;

    public function viewAccess($disable = false)
    {
        if ($this->ajax) {
            return true;
        } else {
            // simple access : we'll allow only 46admin
            global $cookie;
            if ($cookie->profile == 1) {
                return true;
            }
        }

        return false;
    }

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/VersionUtils.php';

        if (!\PrestaShop\Module\AutoUpgrade\VersionUtils::isActualPHPVersionCompatible()) {
            $this->isActualPHPVersionCompatible = false;

            return;
        }

        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('magic_quotes_runtime', '0');
        @ini_set('magic_quotes_sybase', '0');

        $this->init();

        $this->db = Db::getInstance();

        self::$currentIndex = $_SERVER['SCRIPT_NAME'] . (($controller = Tools14::getValue('controller')) ? '?controller=' . $controller : '');

        if (defined('_PS_ADMIN_DIR_')) {
            // Check that the 1-click upgrade working directory is existing or create it
            if (!file_exists($this->autoupgradePath) && !@mkdir($this->autoupgradePath)) {
                $this->_errors[] = $this->trans('Unable to create the directory "%s"', [$this->autoupgradePath]);

                return;
            }

            // Make sure that the 1-click upgrade working directory is writeable
            if (!is_writable($this->autoupgradePath)) {
                $this->_errors[] = $this->trans('Unable to write in the directory "%s"', [$this->autoupgradePath]);

                return;
            }

            // If a previous version of ajax-upgradetab.php exists, delete it
            if (file_exists($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php')) {
                @unlink($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
            }

            $file_tab = @filemtime($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
            $file = @filemtime(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->autoupgradeDir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');

            if ($file_tab < $file) {
                @copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->autoupgradeDir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php',
                    $this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
            }

            // Make sure that the XML config directory exists
            if (!file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml') &&
                !@mkdir(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml', 0775)) {
                $this->_errors[] = $this->trans('Unable to create the directory "%s"', [_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml']);

                return;
            } else {
                @chmod(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml', 0775);
            }

            // Create a dummy index.php file in the XML config directory to avoid directory listing
            if (!file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'index.php') &&
                (file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php') &&
                    !@copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'index.php'))) {
                $this->_errors[] = $this->trans('Unable to create the directory "%s"', [_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml']);

                return;
            }
        }

        if (!$this->ajax) {
            Context::getContext()->smarty->assign('display_header_javascript', true);
        }
    }

    /**
     * function to set configuration fields display.
     *
     * @return void
     */
    private function _setFields()
    {
        $this->_fieldsBackupOptions = [
            'PS_AUTOUP_BACKUP' => [
                'title' => $this->trans('Back up my files and database'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $this->trans('Automatically back up your database and files in order to restore your shop if needed. This is experimental: you should still perform your own manual backup for safety.'),
            ],
            'PS_AUTOUP_KEEP_IMAGES' => [
                'title' => $this->trans('Back up my images'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $this->trans('To save time, you can decide not to back your images up. In any case, always make sure you did back them up manually.'),
            ],
        ];
        $this->_fieldsUpgradeOptions = [
            'PS_AUTOUP_PERFORMANCE' => [
                'title' => $this->trans('Server performance'),
                'cast' => 'intval',
                'validation' => 'isInt',
                'defaultValue' => '1',
                'type' => 'select',
                'desc' => $this->trans('Unless you are using a dedicated server, select "Low".') . '<br />' .
                    $this->trans('A high value can cause the upgrade to fail if your server is not powerful enough to process the upgrade tasks in a short amount of time.'),
                'choices' => [1 => $this->trans('Low (recommended)'), 2 => $this->trans('Medium'), 3 => $this->trans('High')],
            ],
            'PS_AUTOUP_CUSTOM_MOD_DESACT' => [
                'title' => $this->trans('Disable non-native modules'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'type' => 'bool',
                'desc' => $this->trans('As non-native modules can experience some compatibility issues, we recommend to disable them by default.') . '<br />' .
                    $this->trans('Keeping them enabled might prevent you from loading the "Modules" page properly after the upgrade.'),
            ],
            'PS_DISABLE_OVERRIDES' => [
                'title' => $this->trans('Disable all overrides'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'type' => 'bool',
                'desc' => $this->trans('Enable or disable all classes and controllers overrides.'),
            ],
            'PS_AUTOUP_UPDATE_DEFAULT_THEME' => [
                'title' => $this->trans('Upgrade the default theme'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $this->trans('If you customized the default PrestaShop theme in its folder (folder name "classic" in 1.7), enabling this option will lose your modifications.') . '<br />'
                    . $this->trans('If you are using your own theme, enabling this option will simply update the default theme files, and your own theme will be safe.'),
            ],
            'PS_AUTOUP_UPDATE_RTL_FILES' => [
                'title' => $this->trans('Regenerate RTL stylesheet'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $this->trans('If enabled, any RTL-specific files that you might have added to all your themes might be deleted by the created stylesheet.'),
            ],
            'PS_AUTOUP_CHANGE_DEFAULT_THEME' => [
                'title' => $this->trans('Switch to the default theme'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '0',
                'type' => 'bool',
                'desc' => $this->trans('This will change your theme: your shop will then use the default theme of the version of PrestaShop you are upgrading to.'),
            ],
            'PS_AUTOUP_KEEP_MAILS' => [
                'title' => $this->trans('Keep the customized email templates'),
                'cast' => 'intval',
                'validation' => 'isBool',
                'type' => 'bool',
                'desc' => $this->trans('This will not upgrade the default PrestaShop e-mails.') . '<br />'
                    . $this->trans('If you customized the default PrestaShop e-mail templates, enabling this option will keep your modifications.'),
            ],
        ];
    }

    /**
     * init to build informations we need.
     *
     * @return void
     */
    public function init()
    {
        if (!$this->isActualPHPVersionCompatible) {
            parent::init();

            return;
        }

        if (!$this->ajax) {
            parent::init();
        }

        // V9 context security
        // After an upgrade we disconnect the user from the session, and the employee context is null.
        if (!$this->context->employee->id) {
            return;
        }

        // For later use, let's set up prodRootDir and adminDir
        // This way it will be easier to upgrade a different path if needed
        $this->prodRootDir = _PS_ROOT_DIR_;
        $this->adminDir = realpath(_PS_ADMIN_DIR_);
        $this->upgradeContainer = new UpgradeContainer($this->prodRootDir, $this->adminDir);
        if (!defined('__PS_BASE_URI__')) {
            // _PS_DIRECTORY_ replaces __PS_BASE_URI__ in 1.5
            if (defined('_PS_DIRECTORY_')) {
                define('__PS_BASE_URI__', _PS_DIRECTORY_);
            } else {
                define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])) . '/../../');
            }
        }
        // from $_POST or $_GET
        $this->action = empty($_REQUEST['action']) ? null : $_REQUEST['action'];
        $this->initPath();
        $this->upgradeContainer->getState()->importFromArray(
            empty($_REQUEST['params']) ? [] : $_REQUEST['params']
        );

        // If you have defined this somewhere, you know what you do
        // load options from configuration if we're not in ajax mode
        if (!$this->ajax) {
            $upgrader = $this->upgradeContainer->getUpgrader();
            $this->upgradeContainer->getCookie()->create(
                $this->context->employee->id,
                $this->context->language->iso_code
            );

            $this->upgradeContainer->getState()->initDefault(
                $upgrader,
                $this->upgradeContainer->getProperty(UpgradeContainer::PS_ROOT_PATH),
                $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION));

            if (isset($_GET['refreshCurrentVersion'])) {
                $upgradeConfiguration = $this->upgradeContainer->getUpgradeConfiguration();
                // delete the potential xml files we saved in config/xml (from last release and from current)
                $upgrader->clearXmlMd5File($this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION));
                $upgrader->clearXmlMd5File($upgrader->version_num);
                if ($upgradeConfiguration->get('channel') == 'private' && !$upgradeConfiguration->get('private_allow_major')) {
                    $upgrader->checkPSVersion(true, ['private', 'minor']);
                } else {
                    $upgrader->checkPSVersion(true, ['minor']);
                }
                Tools14::redirectAdmin(self::$currentIndex . '&conf=5&token=' . Tools14::getValue('token'));
            }
            // removing temporary files
            $this->upgradeContainer->getFileConfigurationStorage()->cleanAll();
        }
    }

    /**
     * create some required directories if they does not exists.
     *
     * @return void
     */
    public function initPath()
    {
        $this->upgradeContainer->getWorkspace()->createFolders();

        // set autoupgradePath, to be used in backupFiles and backupDb config values
        $this->autoupgradePath = $this->adminDir . DIRECTORY_SEPARATOR . $this->autoupgradeDir;
        $this->backupPath = $this->autoupgradePath . DIRECTORY_SEPARATOR . 'backup';
        $this->downloadPath = $this->autoupgradePath . DIRECTORY_SEPARATOR . 'download';
        $this->latestPath = $this->autoupgradePath . DIRECTORY_SEPARATOR . 'latest';
        $this->tmpPath = $this->autoupgradePath . DIRECTORY_SEPARATOR . 'tmp';

        if (!file_exists($this->backupPath . DIRECTORY_SEPARATOR . 'index.php')) {
            if (!copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php', $this->backupPath . DIRECTORY_SEPARATOR . 'index.php')) {
                $this->_errors[] = $this->trans('Unable to create file %s', [$this->backupPath . DIRECTORY_SEPARATOR . 'index.php']);
            }
        }

        $tmp = "order deny,allow\ndeny from all";
        if (!file_exists($this->backupPath . DIRECTORY_SEPARATOR . '.htaccess')) {
            if (!file_put_contents($this->backupPath . DIRECTORY_SEPARATOR . '.htaccess', $tmp)) {
                $this->_errors[] = $this->trans('Unable to create file %s', [$this->backupPath . DIRECTORY_SEPARATOR . '.htaccess']);
            }
        }
    }

    public function postProcess()
    {
        if (!$this->isActualPHPVersionCompatible) {
            return true;
        }

        $this->_setFields();

        if (Tools14::isSubmit('customSubmitAutoUpgrade')) {
            $this->handleCustomSubmitAutoUpgradeForm();
        }

        if (Tools14::isSubmit('deletebackup')) {
            $this->handleDeletebackupForm();
        }
        parent::postProcess();

        return true;
    }

    /**
     * @return void
     */
    private function handleDeletebackupForm()
    {
        $res = false;
        $name = Tools14::getValue('name');
        $filelist = scandir($this->backupPath);
        foreach ($filelist as $filename) {
            // the following will match file or dir related to the selected backup
            if (!empty($filename) && $filename[0] != '.' && $filename != 'index.php' && $filename != '.htaccess'
                && preg_match('#^(auto-backupfiles_|)' . preg_quote($name) . '(\.zip|)$#', $filename)) {
                if (is_file($this->backupPath . DIRECTORY_SEPARATOR . $filename)) {
                    $res &= unlink($this->backupPath . DIRECTORY_SEPARATOR . $filename);
                } elseif (!empty($name) && is_dir($this->backupPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR)) {
                    $res = FilesystemAdapter::deleteDirectory($this->backupPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR);
                }
            }
        }
        if ($res) {
            Tools14::redirectAdmin(self::$currentIndex . '&conf=1&token=' . Tools14::getValue('token'));
        } else {
            $this->_errors[] = $this->trans('Error when trying to delete backups %s', [$name]);
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function handleCustomSubmitAutoUpgradeForm()
    {
        $config_keys = array_keys(array_merge($this->_fieldsUpgradeOptions, $this->_fieldsBackupOptions));
        $config = [];
        foreach ($config_keys as $key) {
            if (!isset($_POST[$key])) {
                continue;
            }
            // The PS_DISABLE_OVERRIDES variable must only be updated on the database side
            if ($key === 'PS_DISABLE_OVERRIDES') {
                UpgradeConfiguration::updatePSDisableOverrides((bool) $_POST[$key]);
            } else {
                $config[$key] = $_POST[$key];
            }
        }

        $UpConfig = $this->upgradeContainer->getUpgradeConfiguration();
        $UpConfig->merge($config);

        if ($this->upgradeContainer->getUpgradeConfigurationStorage()->save(
            $UpConfig,
            UpgradeFileNames::CONFIG_FILENAME)
        ) {
            Tools14::redirectAdmin(self::$currentIndex . '&conf=6&token=' . Tools14::getValue('token'));
        }
    }

    /**
     * @return string
     */
    public function initContent()
    {
        if (!$this->isActualPHPVersionCompatible) {
            $templateData = [
                'message' => $this->trans(
                    'The module %s requires PHP %s to work properly. Please upgrade your server configuration.',
                    [$this->module->displayName, \PrestaShop\Module\AutoUpgrade\VersionUtils::getHumanReadableVersionOf(\PrestaShop\Module\AutoUpgrade\VersionUtils::MODULE_COMPATIBLE_PHP_VERSION)]
                ),
            ];

            try {
                global $kernel;
                $twigLoader = $kernel->getContainer()->get('twig.loader');
                if (method_exists($twigLoader, 'addPath')) {
                    $twigLoader->addPath('../modules/autoupgrade/views/templates', 'ModuleAutoUpgrade');
                }
                $twig = $kernel->getContainer()->get('twig');
                $this->content = $twig->render('@ModuleAutoUpgrade/error.html.twig', $templateData);
            } catch (Exception $e) {
                $this->displayWarning($templateData['message']);
            }

            return parent::initContent();
        }

        // update backup name
        $backupFinder = new BackupFinder($this->backupPath);
        $availableBackups = $backupFinder->getAvailableBackups();
        if (!$this->upgradeContainer->getUpgradeConfiguration()->shouldBackupFilesAndDatabase()
            && !empty($availableBackups)
            && !in_array($this->upgradeContainer->getState()->getBackupName(), $availableBackups)
        ) {
            $this->upgradeContainer->getState()->setBackupName(end($availableBackups));
        }

        $upgrader = $this->upgradeContainer->getUpgrader();
        $distributionApiService = new DistributionApiService();
        $upgradeSelfCheck = new UpgradeSelfCheck(
            $upgrader,
            $this->upgradeContainer->getPrestaShopConfiguration(),
            $distributionApiService,
            $this->prodRootDir,
            $this->adminDir,
            $this->autoupgradePath
        );
        $response = new AjaxResponse($this->upgradeContainer->getState(), $this->upgradeContainer->getLogger());
        $this->content = (new UpgradePage(
            $this->upgradeContainer->getUpgradeConfiguration(),
            $this->upgradeContainer->getTwig(),
            $this->upgradeContainer->getTranslator(),
            $upgradeSelfCheck,
            $upgrader,
            $backupFinder,
            $this->autoupgradePath,
            $this->prodRootDir,
            $this->adminDir,
            self::$currentIndex,
            $this->token,
            $this->upgradeContainer->getState()->getBackupName(),
            $this->downloadPath
        ))->display(
            $response
                ->setUpgradeConfiguration($this->upgradeContainer->getUpgradeConfiguration())
                ->getJson()
        );

        return parent::initContent();
    }
}
