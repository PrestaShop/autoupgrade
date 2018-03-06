<?php
/*
* 2007-2016 PrestaShop
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
*	@author PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2016 PrestaShop SA
*	@version	Release: $Revision: 11834 $
*	@license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\BackupFinder;
use PrestaShop\Module\AutoUpgrade\State;
use PrestaShop\Module\AutoUpgrade\UpgradePage;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\PrestashopConfiguration;
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\Module\AutoUpgrade\ZipAction;
use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Twig\TransFilterExtension;

require __DIR__.'/vendor/autoload.php';

class AdminSelfUpgrade extends AdminController
{
    public $multishop_context;
    public $multishop_context_group = false;
    public $_html = '';
    // used for translations
    public static $l_cache;
    // retrocompatibility
    public $noTabLink = array();
    public $id = -1;

    public $ajax = false;
    public $next = 'N/A';

    public $upgrader = null;
    public $standalone = true;

    /**
     * set to false if the current step is a loop
     *
     * @var boolean
     */
    public $stepDone = true;
    public $status = true;
    public $error = '0';
    public $nextParams = array();
    public $currentParams = array();

    /**
     * Initialized in initPath()
     */
    public $autoupgradePath = null;
    public $downloadPath = null;
    public $backupPath = null;
    public $latestPath = null;
    public $tmpPath = null;

    /**
     * autoupgradeDir
     *
     * @var string directory relative to admin dir
     */
    public $autoupgradeDir = 'autoupgrade';
    public $latestRootDir = '';
    public $prodRootDir = '';
    public $adminDir = '';

    public $destDownloadFilename = 'prestashop.zip';

    public $keepImages = null;
    public $updateDefaultTheme = null;
    public $changeToDefaultTheme = null;
    public $keepMails = null;
    public $manualMode = null;
    public $deactivateCustomModule = null;

    private $restoreIgnoreFiles = array();
    private $restoreIgnoreAbsoluteFiles = array();
    private $backupIgnoreFiles = array();
    private $backupIgnoreAbsoluteFiles = array();
    private $excludeFilesFromUpgrade = array();
    private $excludeAbsoluteFilesFromUpgrade = array();

    public static $classes14 = array('Cache', 'CacheFS', 'CarrierModule', 'Db', 'FrontController', 'Helper','ImportModule',
    'MCached', 'Module', 'ModuleGraph', 'ModuleGraphEngine', 'ModuleGrid', 'ModuleGridEngine',
    'MySQL', 'Order', 'OrderDetail', 'OrderDiscount', 'OrderHistory', 'OrderMessage', 'OrderReturn',
    'OrderReturnState', 'OrderSlip', 'OrderState', 'PDF', 'RangePrice', 'RangeWeight', 'StockMvt',
    'StockMvtReason', 'SubDomain', 'Shop', 'Tax', 'TaxRule', 'TaxRulesGroup', 'WebserviceKey', 'WebserviceRequest', '');

    public static $loopBackupFiles = 400;
    public static $maxBackupFileSize = 15728640; // 15 Mo
    public static $loopBackupDbTime = 6;
    public static $max_written_allowed = 4194304; // 4096 ko
    public static $loopUpgradeFiles = 600;
    public static $loopRestoreFiles = 400;
    public static $loopRestoreQueryTime = 6;
    public static $loopUpgradeModulesTime = 6;
    public static $loopRemoveSamples = 400;

    /* usage :  key = the step you want to ski
     * value = the next step you want instead
     *	example : public static $skipAction = array();
     *	initial order upgrade:
     *		download, unzip, removeSamples, backupFiles, backupDb, upgradeFiles, upgradeDb, upgradeModules, cleanDatabase, upgradeComplete
     * initial order rollback: rollback, restoreFiles, restoreDb, rollbackComplete
     */
    public static $skipAction = array();

    public $_fieldsUpgradeOptions = array();
    public $_fieldsBackupOptions = array();

    /////////////////////////////////////////////////
    // CLASS INSTANCES
    /////////////////////////////////////////////////

    /**
     * @var Db
     */
    public $db;

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
     * @var ZipAction
     */
    private $zipAction;

    /**
     * @var FileConfigurationStorage
     */
    private $fileConfigurationStorage;

    /////////////////////////////////////////////////
    // END OF CLASS INSTANCES
    /////////////////////////////////////////////////


    /**
     * replace tools encrypt
     *
     * @param mixed $string
     * @return void
     */
    public function encrypt($string)
    {
        return md5(_COOKIE_KEY_.$string);
    }

    public function checkToken()
    {
        // simple checkToken in ajax-mode, to be free of Cookie class (and no Tools::encrypt() too )
        if ($this->ajax && isset($_COOKIE['id_employee'])) {
            return ($_COOKIE['autoupgrade'] == $this->encrypt($_COOKIE['id_employee']));
        } else {
            return parent::checkToken();
        }
    }

    /**
     * create cookies id_employee, id_tab and autoupgrade (token)
     */
    public function createCustomToken()
    {
        // ajax-mode for autoupgrade, we can't use the classic authentication
        // so, we'll create a cookie in admin dir, based on cookie key
        global $cookie;
        $id_employee = $cookie->id_employee;
        if ($cookie->id_lang) {
            $iso_code = $_COOKIE['iso_code'] = Language::getIsoById((int)$cookie->id_lang);
        } else {
            $iso_code = 'en';
        }
        $admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
        $cookiePath = __PS_BASE_URI__.$admin_dir;
        setcookie('id_employee', $id_employee, 0, $cookiePath);
        setcookie('id_tab', $this->id, 0, $cookiePath);
        setcookie('iso_code', $iso_code, 0, $cookiePath);
        setcookie('autoupgrade', $this->encrypt($id_employee), 0, $cookiePath);
        return false;
    }

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
        parent::__construct();
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('magic_quotes_runtime', '0');
        @ini_set('magic_quotes_sybase', '0');

        $this->init();

        $this->db = Db::getInstance();
        $this->bootstrap = true;

        // Init PrestashopCompliancy class
        $admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
        $this->prestashopConfiguration = new PrestashopConfiguration(
            $admin_dir.DIRECTORY_SEPARATOR.$this->autoupgradeDir,
            $this->getUpgrader()->autoupgrade_last_version
        );

        // Performance settings, if your server has a low memory size, lower these values
        $perf_array = array(
            'loopBackupFiles' => array(400, 800, 1600),
            'maxBackupFileSize' => array(15728640, 31457280, 62914560),
            'loopBackupDbTime' => array(6, 12, 25),
            'max_written_allowed' => array(4194304, 8388608, 16777216),
            'loopUpgradeFiles' => array(600, 1200, 2400),
            'loopRestoreFiles' => array(400, 800, 1600),
            'loopRestoreQueryTime' => array(6, 12, 25),
            'loopUpgradeModulesTime' => array(6, 12, 25),
            'loopRemoveSamples' => array(400, 800, 1600)
        );
        switch ($this->upgradeConfiguration->get('PS_AUTOUP_PERFORMANCE')) {
            case 3:
                foreach ($perf_array as $property => $values) {
                    self::$$property = $values[2];
                }
                break;
            case 2:
                foreach ($perf_array as $property => $values) {
                    self::$$property = $values[1];
                }
                break;
            case 1:
            default:
                foreach ($perf_array as $property => $values) {
                    self::$$property = $values[0];
                }
        }

        self::$currentIndex = $_SERVER['SCRIPT_NAME'].(($controller = Tools14::getValue('controller')) ? '?controller='.$controller: '');

        if (defined('_PS_ADMIN_DIR_')) {
            $file_tab = @filemtime($this->autoupgradePath.DIRECTORY_SEPARATOR.'ajax-upgradetab.php');
            $file =  @filemtime(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->autoupgradeDir.DIRECTORY_SEPARATOR.'ajax-upgradetab.php');

            if ($file_tab < $file) {
                @copy(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->autoupgradeDir.DIRECTORY_SEPARATOR.'ajax-upgradetab.php',
                    $this->autoupgradePath.DIRECTORY_SEPARATOR.'ajax-upgradetab.php');
            }
        }

        if (!$this->ajax) {
            Context::getContext()->smarty->assign('display_header_javascript', true);
        }
    }

    /**
     * function to set configuration fields display
     *
     * @return void
     */
    private function _setFields()
    {
        $this->_fieldsBackupOptions = array(
            'PS_AUTOUP_BACKUP' => array(
                'title' => $this->trans('Back up my files and database', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '1',
                'type' => 'bool', 'desc' => $this->trans('Automatically back up your database and files in order to restore your shop if needed. This is experimental: you should still perform your own manual backup for safety.', array(), 'Modules.Autoupgrade.Admin'),
            ),
            'PS_AUTOUP_KEEP_IMAGES' => array(
                'title' => $this->trans('Back up my images', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '1',
                'type' => 'bool', 'desc' => $this->trans('To save time, you can decide not to back your images up. In any case, always make sure you did back them up manually.', array(), 'Modules.Autoupgrade.Admin'),
            ),
        );
        $this->_fieldsUpgradeOptions = array(
            'PS_AUTOUP_PERFORMANCE' => array(
                'title' => $this->trans('Server performance', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isInt', 'defaultValue' => '1',
                'type' => 'select', 'desc' => $this->trans('Unless you are using a dedicated server, select "Low".', array(), 'Modules.Autoupgrade.Admin').'<br />'.
                $this->trans('A high value can cause the upgrade to fail if your server is not powerful enough to process the upgrade tasks in a short amount of time.', array(), 'Modules.Autoupgrade.Admin'),
                'choices' => array(1 => $this->trans('Low (recommended)', array(), 'Modules.Autoupgrade.Admin'), 2 => $this->trans('Medium', array(), 'Modules.Autoupgrade.Admin'), 3 => $this->trans('High', array(), 'Modules.Autoupgrade.Admin'))
            ),
            'PS_AUTOUP_CUSTOM_MOD_DESACT' => array(
                'title' => $this->trans('Disable non-native modules', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isBool',
                'type' => 'bool', 'desc' => $this->trans('As non-native modules can experience some compatibility issues, we recommend to disable them by default.', array(), 'Modules.Autoupgrade.Admin').'<br />'.
                $this->trans('Keeping them enabled might prevent you from loading the "Modules" page properly after the upgrade.', array(), 'Modules.Autoupgrade.Admin'),
            ),
            'PS_AUTOUP_UPDATE_DEFAULT_THEME' => array(
                'title' => $this->trans('Upgrade the default theme', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '1',
                'type' => 'bool', 'desc' => $this->trans('If you customized the default PrestaShop theme in its folder (folder name "classic" in 1.7), enabling this option will lose your modifications.', array(), 'Modules.Autoupgrade.Admin').'<br />'
                .$this->trans('If you are using your own theme, enabling this option will simply update the default theme files, and your own theme will be safe.', array(), 'Modules.Autoupgrade.Admin'),
            ),
            'PS_AUTOUP_CHANGE_DEFAULT_THEME' => array(
                'title' => $this->trans('Switch to the default theme', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '0',
                'type' => 'bool', 'desc' => $this->trans('This will change your theme: your shop will then use the default theme of the version of PrestaShop you are upgrading to.', array(), 'Modules.Autoupgrade.Admin'),
            ),
            'PS_AUTOUP_KEEP_MAILS' => array(
                'title' => $this->trans('Keep the customized email templates', array(), 'Modules.Autoupgrade.Admin'), 'cast' => 'intval', 'validation' => 'isBool',
                'type' => 'bool', 'desc' => $this->trans('This will not upgrade the default PrestaShop e-mails.', array(), 'Modules.Autoupgrade.Admin').'<br />'
                .$this->trans('If you customized the default PrestaShop e-mail templates, enabling this option will keep your modifications.', array(), 'Modules.Autoupgrade.Admin'),
            ),
        );
    }

    /**
     * init to build informations we need
     *
     * @return void
     */
    public function init()
    {
        if (!$this->ajax) {
            parent::init();
        }
        // For later use, let's set up prodRootDir and adminDir
        // This way it will be easier to upgrade a different path if needed
        $this->prodRootDir = _PS_ROOT_DIR_;
        $this->adminDir = _PS_ADMIN_DIR_;
        if (!defined('__PS_BASE_URI__')) {
            // _PS_DIRECTORY_ replaces __PS_BASE_URI__ in 1.5
            if (defined('_PS_DIRECTORY_')) {
                define('__PS_BASE_URI__', _PS_DIRECTORY_);
            } else {
                define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])).'/../../');
            }
        }
        // from $_POST or $_GET
        $this->action = empty($_REQUEST['action'])?null:$_REQUEST['action'];
        $this->currentParams = empty($_REQUEST['params'])?array():$_REQUEST['params'];
        // test writable recursively
        if (!class_exists('ConfigurationTest', false)) {
            require_once(dirname(__FILE__).'/classes/ConfigurationTest.php');
            if (!class_exists('ConfigurationTest', false) and class_exists('ConfigurationTestCore')) {
                eval('class ConfigurationTest extends ConfigurationTestCore{}');
            }
        }
        $this->initPath();
        $this->getUpgradeConfiguration();
        $this->getState();

        // If you have defined this somewhere, you know what you do
        /* load options from configuration if we're not in ajax mode */
        if (!$this->ajax) {
            $this->createCustomToken();

            $this->initDefaultState();
            // removing temporary files
            $this->getFileConfigurationStorage()->cleanAll();
        }


        $this->keepImages = $this->upgradeConfiguration->get('PS_AUTOUP_KEEP_IMAGES');
        $this->updateDefaultTheme = $this->upgradeConfiguration->get('PS_AUTOUP_UPDATE_DEFAULT_THEME');
        $this->changeToDefaultTheme = $this->upgradeConfiguration->get('PS_AUTOUP_CHANGE_DEFAULT_THEME');
        $this->keepMails = $this->upgradeConfiguration->get('PS_AUTOUP_KEEP_MAILS');
        $this->manualMode = (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_)? (bool)$this->upgradeConfiguration->get('PS_AUTOUP_MANUAL_MODE') : false;
        $this->deactivateCustomModule = $this->upgradeConfiguration->get('PS_AUTOUP_CUSTOM_MOD_DESACT');

        // during restoration, do not remove :
        $this->restoreIgnoreAbsoluteFiles[] = '/app/config/parameters.php';
        $this->restoreIgnoreAbsoluteFiles[] = '/app/config/parameters.yml';
        $this->restoreIgnoreAbsoluteFiles[] = '/modules/autoupgrade';
        $this->restoreIgnoreAbsoluteFiles[] = '/admin/autoupgrade';
        $this->restoreIgnoreAbsoluteFiles[] = '.';
        $this->restoreIgnoreAbsoluteFiles[] = '..';

        // during backup, do not save
        $this->backupIgnoreAbsoluteFiles[] = '/app/cache';
        $this->backupIgnoreAbsoluteFiles[] = '/cache/smarty/compile';
        $this->backupIgnoreAbsoluteFiles[] = '/cache/smarty/cache';
        $this->backupIgnoreAbsoluteFiles[] = '/cache/tcpdf';
        $this->backupIgnoreAbsoluteFiles[] = '/cache/cachefs';

        // do not care about the two autoupgrade dir we use;
        $this->backupIgnoreAbsoluteFiles[] = '/modules/autoupgrade';
        $this->backupIgnoreAbsoluteFiles[] = '/admin/autoupgrade';

        $this->backupIgnoreFiles[] = '.';
        $this->backupIgnoreFiles[] = '..';
        $this->backupIgnoreFiles[] = '.svn';
        $this->backupIgnoreFiles[] = '.git';
        $this->backupIgnoreFiles[] = $this->autoupgradeDir;

        $this->excludeFilesFromUpgrade[] = '.';
        $this->excludeFilesFromUpgrade[] = '..';
        $this->excludeFilesFromUpgrade[] = '.svn';
        $this->excludeFilesFromUpgrade[] = '.git';

        // do not copy install, neither app/config/parameters.php in case it would be present
        $this->excludeAbsoluteFilesFromUpgrade[] = '/app/config/parameters.php';
        $this->excludeAbsoluteFilesFromUpgrade[] = '/app/config/parameters.yml';
        $this->excludeAbsoluteFilesFromUpgrade[] = '/install';
        $this->excludeAbsoluteFilesFromUpgrade[] = '/install-dev';

        // this will exclude autoupgrade dir from admin, and autoupgrade from modules
        $this->excludeFilesFromUpgrade[] = $this->autoupgradeDir;

        if ($this->keepImages === '0') {
            $this->backupIgnoreAbsoluteFiles[] = '/img';
            $this->restoreIgnoreAbsoluteFiles[] = '/img';
        } else {
            $this->backupIgnoreAbsoluteFiles[] = '/img/tmp';
            $this->restoreIgnoreAbsoluteFiles[] = '/img/tmp';
        }

        if (!$this->updateDefaultTheme) /* If set to false, we need to preserve the default themes */
        {
            $this->excludeAbsoluteFilesFromUpgrade[] = '/themes/classic';
        }
    }

    public function initDefaultState()
    {
        $postData = 'version='._PS_VERSION_.'&method=listing&action=native&iso_code=all';
        $xml_local = $this->prodRootDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'modules_native_addons.xml';
        $xml = $this->getUpgrader()->getApiAddons($xml_local, $postData, true);

        $modules_addons = array();
        if (is_object($xml)) {
            foreach ($xml as $mod) {
                $modules_addons[(string)$mod->id] = (string)$mod->name;
            }
        }
        $this->state->setModulesAddons($modules_addons);

        // installedLanguagesIso is used to merge translations files
        $installedLanguagesIso = array_map(
            function($v) { return $v['iso_code']; },
            Language::getIsoIds(false)
        );
        $this->state->setInstalledLanguagesIso($installedLanguagesIso);

        $rand = dechex(mt_rand(0, min(0xffffffff, mt_getrandmax())));
        $date = date('Ymd-His');
        $backupName = 'V'._PS_VERSION_.'_'.$date.'-'.$rand;
        // Todo: To be moved in state class? We could only require the backup name here
        // I.e = $this->state->setBackupName($backupName);, which triggers 2 other setters internally
        $this->state->setBackupName($backupName)
            ->setBackupFilesFilename('auto-backupfiles_'.$backupName.'.zip')
            ->setBackupDbFilename('auto-backupdb_XXXXXX_'.$backupName.'.sql');
    }

    /**
     * create some required directories if they does not exists
     *
     * Also set nextParams (removeList and filesToUpgrade) if they
     * exists in currentParams
     *
     */
    public function initPath()
    {
        // If not exists in this sessions, "create"
        // session handling : from current to next params
        foreach (array('removeList', 'filesToUpgrade', 'modulesToUpgrade') as $attr) {
            if (isset($this->currentParams[$attr])) {
                $this->nextParams[$attr] = $this->currentParams[$attr];
            }
        }

        // set autoupgradePath, to be used in backupFiles and backupDb config values
        $this->autoupgradePath = $this->adminDir.DIRECTORY_SEPARATOR.$this->autoupgradeDir;
        $this->backupPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'backup';
        $this->downloadPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'download';
        $this->latestPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'latest';
        $this->tmpPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'tmp';
        $this->latestRootDir = $this->latestPath.DIRECTORY_SEPARATOR;

        // Check directory is not missing
        foreach (array('autoupgradePath', 'backupPath', 'downloadPath', 'latestPath', 'tmpPath', 'latestRootDir') as $pathName) {
            $path = $this->{$pathName};
            if (!file_exists($path) && !mkdir($path)) {
                $this->_errors[] = $this->trans('Unable to create directory %s', array($path), 'Modules.Autoupgrade.Admin');
            }
            if (!is_writable($path)) {
                $this->_errors[] = $this->trans('Unable to write in the directory "%s"', array($path), 'Modules.Autoupgrade.Admin');
            }
        }

        if (!file_exists($this->backupPath.DIRECTORY_SEPARATOR.'index.php')) {
            if (!copy(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'index.php', $this->backupPath.DIRECTORY_SEPARATOR.'index.php')) {
                $this->_errors[] = $this->trans('Unable to create file %s', array($this->backupPath.DIRECTORY_SEPARATOR.'index.php'), 'Modules.Autoupgrade.Admin');
            }
        }

        $tmp = "order deny,allow\ndeny from all";
        if (!file_exists($this->backupPath.DIRECTORY_SEPARATOR.'.htaccess')) {
            if (!file_put_contents($this->backupPath.DIRECTORY_SEPARATOR.'.htaccess', $tmp)) {
                $this->_errors[] = $this->trans('Unable to create file %s', array($this->backupPath.DIRECTORY_SEPARATOR.'.htaccess'), 'Modules.Autoupgrade.Admin');
            }
        }
    }

    /**
     * getFilePath return the path to the zipfile containing prestashop.
     *
     * @return void
     */
    public function getFilePath()
    {
        return $this->downloadPath.DIRECTORY_SEPARATOR.$this->destDownloadFilename;
    }

    public function postProcess()
    {
        $this->_setFields();

        // set default configuration to default channel & dafault configuration for backup and upgrade
        // (can be modified in expert mode)
        $config = $this->upgradeConfiguration->get('channel');
        if ($config === null) {
            $config = array();
            $config['channel'] = Upgrader::DEFAULT_CHANNEL;
            $this->writeConfig($config);
            if (class_exists('Configuration', false)) {
                Configuration::updateValue('PS_UPGRADE_CHANNEL', $config['channel']);
            }

            $this->writeConfig(array(
                'PS_AUTOUP_PERFORMANCE' => '1',
                'PS_AUTOUP_CUSTOM_MOD_DESACT' => '1',
                'PS_AUTOUP_UPDATE_DEFAULT_THEME' => '1',
                'PS_AUTOUP_CHANGE_DEFAULT_THEME' => '0',
                'PS_AUTOUP_KEEP_MAILS' => '0',
                'PS_AUTOUP_BACKUP' => '1',
                'PS_AUTOUP_KEEP_IMAGES' => '0'
                ));
        }

        if (Tools14::isSubmit('putUnderMaintenance')) {
            foreach (Shop::getCompleteListOfShopsID() as $id_shop) {
                Configuration::updateValue('PS_SHOP_ENABLE', 0, false, null, (int)$id_shop);
            }
            Configuration::updateGlobalValue('PS_SHOP_ENABLE', 0);
        }

        if (Tools14::isSubmit('customSubmitAutoUpgrade')) {
            $config_keys = array_keys(array_merge($this->_fieldsUpgradeOptions, $this->_fieldsBackupOptions));
            $config = array();
            foreach ($config_keys as $key) {
                if (isset($_POST[$key])) {
                    $config[$key] = $_POST[$key];
                }
            }
            $res = $this->writeConfig($config);
            if ($res) {
                Tools14::redirectAdmin(self::$currentIndex.'&conf=6&token='.Tools14::getValue('token'));
            }
        }

        if (Tools14::isSubmit('deletebackup')) {
            $res = false;
            $name = Tools14::getValue('name');
            $filelist = scandir($this->backupPath);
            foreach ($filelist as $filename) {
                // the following will match file or dir related to the selected backup
                if (!empty($filename) && $filename[0] != '.' && $filename != 'index.php' && $filename != '.htaccess'
                    && preg_match('#^(auto-backupfiles_|)'.preg_quote($name).'(\.zip|)$#', $filename, $matches)) {
                    if (is_file($this->backupPath.DIRECTORY_SEPARATOR.$filename)) {
                        $res &= unlink($this->backupPath.DIRECTORY_SEPARATOR.$filename);
                    } elseif (!empty($name) && is_dir($this->backupPath.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR)) {
                        $res = self::deleteDirectory($this->backupPath.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR);
                    }
                }
            }
            if ($res) {
                Tools14::redirectAdmin(self::$currentIndex.'&conf=1&token='.Tools14::getValue('token'));
            } else {
                $this->_errors[] = $this->trans('Error when trying to delete backups %s', array($name), 'Modules.Autoupgrade.Admin');
            }
        }
        parent::postProcess();
    }

    /**
     * update module configuration (saved in file UpgradeFiles::configFilename) with $new_config
     *
     * @param array $new_config
     * @return boolean true if success
     */
    public function writeConfig($config)
    {
        if (!$this->getFileConfigurationStorage()->exists(UpgradeFileNames::configFilename) && !empty($config['channel'])) {
            $this->upgrader->channel = $config['channel'];
            $this->upgrader->checkPSVersion();

            $this->state->setInstallVersion($this->upgrader->version_num);
        }

        $this->upgradeConfiguration->merge($config);
        $this->getLogger()->info($this->trans('Configuration successfully updated.', array(), 'Modules.Autoupgrade.Admin').' <strong>'.$this->trans('This page will now be reloaded and the module will check if a new version is available.', array(), 'Modules.Autoupgrade.Admin').'</strong>');
        return (new UpgradeConfigurationStorage($this->autoupgradePath.DIRECTORY_SEPARATOR))->save($this->upgradeConfiguration, UpgradeFileNames::configFilename);
    }

    /**
     * list files to upgrade and return it as array
     *
     * @param string $dir
     * @return number of files found
     */
    public function _listFilesToUpgrade($dir)
    {
        $list = array();
        if (!is_dir($dir)) {
            $this->getLogger()->error($this->trans('[ERROR] %s does not exist or is not a directory.', array($dir), 'Modules.Autoupgrade.Admin'));
            $this->getLogger()->info($this->trans('Nothing has been extracted. It seems the unzipping step has been skipped.', array(), 'Modules.Autoupgrade.Admin'));
            $this->next = 'error';
            return false;
        }

        $allFiles = scandir($dir);
        foreach ($allFiles as $file) {
            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;

            if ($this->getFilesystemAdapter()->isFileSkipped($file, $fullPath, "upgrade")) {
                if (!in_array($file, array('.', '..'))) {
                    $this->getLogger()->debug($this->trans('File %s is preserved', array($file), 'Modules.Autoupgrade.Admin'));
                }
                continue;
            }
            $list[] = str_replace($this->latestRootDir, '', $fullPath);
            // if is_dir, we will create it :)
            if (is_dir($fullPath) && strpos($dir.DIRECTORY_SEPARATOR.$file, 'install') === false) {
                $list = array_merge($list, $this->_listFilesToUpgrade($fullPath));
            }
        }
        return $list;
    }

    private function createCacheFsDirectories($level_depth, $directory = false)
    {
        if (!$directory) {
            if (!defined('_PS_CACHEFS_DIRECTORY_')) {
                define('_PS_CACHEFS_DIRECTORY_', $this->prodRootDir.'/cache/cachefs/');
            }
            $directory = _PS_CACHEFS_DIRECTORY_;
        }
        $chars = '0123456789abcdef';
        for ($i = 0; $i < strlen($chars); $i++) {
            $new_dir = $directory.$chars[$i].'/';
            if (mkdir($new_dir, 0775) && chmod($new_dir, 0775) && $level_depth - 1 > 0) {
                self::createCacheFsDirectories($level_depth - 1, $new_dir);
            }
        }
    }

    /**
     * upgradeThisFile
     *
     * @param mixed $file
     * @return void
     */
    public function upgradeThisFile($file)
    {
        // translations_custom and mails_custom list are currently not used
        // later, we could handle customization with some kind of diff functions
        // for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)
        $orig = $this->latestRootDir.$file;
        $dest = $this->destUpgradePath.$file;

        if ($this->getFilesystemAdapter()->isFileSkipped($file, $dest, 'upgrade')) {
            $this->getLogger()->debug($this->trans('%s ignored', array($file), 'Modules.Autoupgrade.Admin'));
            return true;
        } else {
            if (is_dir($orig)) {
                // if $dest is not a directory (that can happen), just remove that file
                if (!is_dir($dest) and file_exists($dest)) {
                    unlink($dest);
                    $this->getLogger()->debug($this->trans('[WARNING] File %1$s has been deleted.', array($file), 'Modules.Autoupgrade.Admin'));
                }
                if (!file_exists($dest)) {
                    if (mkdir($dest)) {
                        $this->getLogger()->debug($this->trans('Directory %1$s created.', array($file), 'Modules.Autoupgrade.Admin'));
                        return true;
                    } else {
                        $this->next = 'error';
                        $this->getLogger()->error($this->trans('Error while creating directory %s.', array($dest), 'Modules.Autoupgrade.Admin'));
                        return false;
                    }
                } else { // directory already exists
                    $this->getLogger()->debug($this->trans('Directory %s already exists.', array($file), 'Modules.Autoupgrade.Admin'));
                    return true;
                }
            } elseif (is_file($orig)) {
                $translationAdapter = $this->getTranslationAdapter();
                if ($translationAdapter->isTranslationFile($file) && file_exists($dest)) {
                    $type_trad = $translationAdapter->getTranslationFileType($file);
                    if ($translationAdapter->mergeTranslationFile($orig, $dest, $type_trad)) {
                        $this->getLogger()->info($this->trans('[TRANSLATION] The translation files have been merged into file %s.', array($dest), 'Modules.Autoupgrade.Admin'));
                        return true;
                    }
                    $this->getLogger()->warning($this->trans(
                        '[TRANSLATION] The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                        array('%filename%' => $dest),
                        'Modules.Autoupgrade.Admin'
                    ));
                }

                // upgrade exception were above. This part now process all files that have to be upgraded (means to modify or to remove)
                // delete before updating (and this will also remove deprecated files)
                if (copy($orig, $dest)) {
                    $this->getLogger()->debug($this->trans('Copied %1$s.', array($file), 'Modules.Autoupgrade.Admin'));
                    return true;
                } else {
                    $this->next = 'error';
                    $this->getLogger()->error($this->trans('Error while copying file %s', array($file), 'Modules.Autoupgrade.Admin'));
                    return false;
                }
            } elseif (is_file($dest)) {
                if (file_exists($dest)) {
                    unlink($dest);
                }
                $this->getLogger()->debug(sprintf('removed file %1$s.', $file));
                return true;
            } elseif (is_dir($dest)) {
                if (strpos($dest, DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR) === false) {
                    self::deleteDirectory($dest, true);
                }
                $this->getLogger()->debug(sprintf('removed dir %1$s.', $file));
                return true;
            } else {
                return true;
            }
        }
    }

    /**
     * Delete directory and subdirectories
     *
     * @param string $dirname Directory name
     */
    public static function deleteDirectory($dirname, $delete_self = true)
    {
        return Tools14::deleteDirectory($dirname, $delete_self);
    }

    public function buildAjaxResult()
    {
        $response = new AjaxResponse($this->getTranslator(), $this->state, $this->getLogger());
        return $response->setError($this->error)
            ->setStepDone($this->stepDone)
            ->setNext($this->next)
            ->setNextParams($this->nextParams)
            ->setUpgradeConfiguration($this->upgradeConfiguration)
            ->getJsonResponse();
    }

    public function displayAjax()
    {
        echo $this->buildAjaxResult();
    }

    public function display()
    {
        /* Make sure the user has configured the upgrade options, or set default values */
        $configuration_keys = array(
            'PS_AUTOUP_UPDATE_DEFAULT_THEME' => 1,
            'PS_AUTOUP_CHANGE_DEFAULT_THEME' => 0,
            'PS_AUTOUP_KEEP_MAILS' => 0,
            'PS_AUTOUP_CUSTOM_MOD_DESACT' => 1,
            'PS_AUTOUP_MANUAL_MODE' => 0,
            'PS_AUTOUP_PERFORMANCE' => 1,
        );

        foreach ($configuration_keys as $k => $default_value) {
            if (Configuration::get($k) == '') {
                Configuration::updateValue($k, $default_value);
            }
        }

        // update backup name
        $backupFinder = new BackupFinder($this->backupPath);
        $availableBackups = $backupFinder->getAvailableBackups();
        if (!$this->upgradeConfiguration->get('PS_AUTOUP_BACKUP')
            && !empty($availableBackups)
            && !in_array($this->state->getBackupName(), $availableBackups)
        ) {
            $this->state->setBackupName(end($availableBackups));
        }

        $upgrader = $this->getUpgrader();
        $upgradeSelfCheck = new UpgradeSelfCheck(
            $upgrader,
            $this->prodRootDir,
            $this->adminDir,
            $this->autoupgradePath
        );
        $this->_html = (new UpgradePage(
            $this->upgradeConfiguration,
            $this->getTwig(),
            $this->getTranslator(),
            $upgradeSelfCheck,
            $upgrader,
            $backupFinder,
            $this->autoupgradePath,
            $this->prodRootDir,
            $this->adminDir,
            self::$currentIndex,
            $this->token,
            $this->state->getInstallVersion(),
            $this->manualMode,
            $this->state->getBackupName(),
            $this->downloadPath
        ))->display(
            $this->buildAjaxResult()
        );

        $this->ajax = true;
        $this->content = $this->_html;
        return parent::display();
    }

    public function handleException(UpgradeException $e)
    {
        $logger = $this->getLogger();
        foreach($e->getQuickInfos() as $log) {
            $logger->debug($log);
        }
        if ($e->getSeverity() === UpgradeException::SEVERITY_ERROR) {
            $this->next = 'error';
            $this->error = true;
            $logger->error($e->getMessage());
        }
        if ($e->getSeverity() === UpgradeException::SEVERITY_WARNING) {
            $logger->warning($e->getMessage());
        }
    }

    public function getFileConfigurationStorage()
    {
        if (!is_null($this->fileConfigurationStorage)) {
            return $this->fileConfigurationStorage;
        }

        $this->fileConfigurationStorage = new FileConfigurationStorage($this->autoupgradePath.DIRECTORY_SEPARATOR);
        return $this->fileConfigurationStorage;
    }

    public function getUpgrader()
    {
        if (!is_null($this->upgrader)) {
            return $this->upgrader;
        }
        // in order to not use Tools class
        $upgrader = new Upgrader();
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
        $upgrader->branch = $matches[1];
        $channel = $this->upgradeConfiguration->get('channel');
        switch ($channel) {
            case 'archive':
                $upgrader->channel = 'archive';
                $upgrader->version_num = $this->upgradeConfiguration->get('archive.version_num');
                $this->destDownloadFilename = $this->upgradeConfiguration->get('archive.filename');
                $upgrader->checkPSVersion(true, array('archive'));
                break;
            case 'directory':
                $upgrader->channel = 'directory';
                $upgrader->version_num = $this->upgradeConfiguration->get('directory.version_num');
                $upgrader->checkPSVersion(true, array('directory'));
                break;
            default:
                $upgrader->channel = $channel;
                if (isset($_GET['refreshCurrentVersion'])) {
                    // delete the potential xml files we saved in config/xml (from last release and from current)
                    $upgrader->clearXmlMd5File(_PS_VERSION_);
                    $upgrader->clearXmlMd5File($upgrader->version_num);
                    if ($this->upgradeConfiguration->get('channel') == 'private' && !$this->upgradeConfiguration->get('private_allow_major')) {
                        $upgrader->checkPSVersion(true, array('private', 'minor'));
                    } else {
                        $upgrader->checkPSVersion(true, array('minor'));
                    }
                    Tools14::redirectAdmin(self::$currentIndex.'&conf=5&token='.Tools14::getValue('token'));
                } else {
                    if ($this->upgradeConfiguration->get('channel') == 'private' && !$this->upgradeConfiguration->get('private_allow_major')) {
                        $upgrader->checkPSVersion(false, array('private', 'minor'));
                    } else {
                        $upgrader->checkPSVersion(false, array('minor'));
                    }
                }
        }
        $this->state->setInstallVersion($upgrader->version_num);
        $this->upgrader = $upgrader;
        return $this->upgrader;
    }

    public function getFilesystemAdapter()
    {
        if (!is_null($this->filesystemAdapter)) {
            return $this->filesystemAdapter;
        }

        $this->filesystemAdapter = new FilesystemAdapter(
            $this->backupIgnoreAbsoluteFiles, $this->backupIgnoreFiles,
            $this->excludeAbsoluteFilesFromUpgrade, $this->excludeFilesFromUpgrade,
            $this->state->getRestoreFilesFilename(), $this->restoreIgnoreAbsoluteFiles,
            $this->restoreIgnoreFiles, $this->autoupgradeDir,
            str_replace($this->prodRootDir, '', $this->adminDir), $this->prodRootDir);

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

        $logFile = $this->tmpPath.DIRECTORY_SEPARATOR.'log.txt';
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
            $this->db,
            $this->getTranslator(),
            $this->prodRootDir.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR,
            $this->tmpPath,
            $this->state->getInstallVersion(),
            $this->getZipAction());

        return $this->moduleAdapter;
    }

    public function getState()
    {
        if (!is_null($this->state)) {
            return $this->state;
        }

        $this->state = (new State())->importFromArray($this->currentParams);
        return $this->state;
    }

    public function getTranslationAdapter()
    {
        return new Translation($this->getTranslator(), $this->getLogger(), $this->state->getInstalledLanguagesIso());
    }

    public function getTranslator()
    {
        return new \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator(get_class());
    }

    public function getTwig()
    {
        if (!is_null($this->twig)) {
            return $this->twig;
        }

        // Using independant template engine for 1.6 & 1.7 compatibility
        $loader = new Twig_Loader_Filesystem();
        $loader->addPath(realpath(__DIR__).'/views/templates', 'ModuleAutoUpgrade');
        $twig = new Twig_Environment($loader, array(
            //'cache' => '/path/to/compilation_cache',
        ));
        $twig->addExtension(new TransFilterExtension($this->getTranslator()));

        $this->twig = $twig;
        return $this->twig;
    }

    public function getUpgradeConfiguration()
    {
        if (!is_null($this->upgradeConfiguration)) {
            return $this->upgradeConfiguration;
        }
        $upgradeConfigurationStorage = new UpgradeConfigurationStorage($this->autoupgradePath.DIRECTORY_SEPARATOR);
        $this->upgradeConfiguration = $upgradeConfigurationStorage->load(UpgradeFileNames::configFilename);
        return $this->upgradeConfiguration;
    }

    public function getZipAction()
    {
        if (!is_null($this->zipAction)) {
            return $this->zipAction;
        }

        $this->zipAction = new ZipAction($this->getTranslator(), $this->getLogger(), $this->prodRootDir);
        return $this->zipAction;
    }

    /**
     * Adapter for trans calls, existing only on PS 1.7.
     * Making them available for PS 1.6 as well
     *
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }
}
