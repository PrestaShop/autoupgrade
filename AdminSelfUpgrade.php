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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\ThemeAdapter;
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
    public $next_desc = '';
    public $nextParams = array();
    public $nextQuickInfo = array();
    public $nextErrors = array();
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

    /**
     * if set to true, will use pclZip library
     * even if ZipArchive is available
     */
    public static $force_pclZip = false;

    protected $_includeContainer = true;

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
        $this->next_desc = $this->trans('Configuration successfully updated.', array(), 'Modules.Autoupgrade.Admin').' <strong>'.$this->trans('This page will now be reloaded and the module will check if a new version is available.', array(), 'Modules.Autoupgrade.Admin').'</strong>';
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
            $this->nextQuickInfo[] = $this->trans('[ERROR] %s does not exist or is not a directory.', array($dir), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('[ERROR] %s does not exist or is not a directory.', array($dir), 'Modules.Autoupgrade.Admin');
            $this->next_desc = $this->trans('Nothing has been extracted. It seems the unzipping step has been skipped.', array(), 'Modules.Autoupgrade.Admin');
            $this->next = 'error';
            return false;
        }

        $allFiles = scandir($dir);
        foreach ($allFiles as $file) {
            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;

            if ($this->getFilesystemAdapter()->isFileSkipped($file, $fullPath, "upgrade")) {
                if (!in_array($file, array('.', '..'))) {
                    $this->nextQuickInfo[] = $this->trans('File %s is preserved', array($file), 'Modules.Autoupgrade.Admin');
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
     * This function now replaces doUpgrade.php or upgrade.php
     *
     * @return void
     */
    public function doUpgrade()
    {
        // Initialize
        // setting the memory limit to 128M only if current is lower
        $memory_limit = ini_get('memory_limit');
        if ((substr($memory_limit, -1) != 'G')
            && ((substr($memory_limit, -1) == 'M' and substr($memory_limit, 0, -1) < 128)
                || is_numeric($memory_limit) and (intval($memory_limit) < 131072))
        ) {
            @ini_set('memory_limit', '128M');
        }

        /* Redefine REQUEST_URI if empty (on some webservers...) */
        if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
            if (!isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['SCRIPT_FILENAME'])) {
                $_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_FILENAME'];
            }
            if (isset($_SERVER['SCRIPT_NAME'])) {
                if (basename($_SERVER['SCRIPT_NAME']) == 'index.php' && empty($_SERVER['QUERY_STRING'])) {
                    $_SERVER['REQUEST_URI'] = dirname($_SERVER['SCRIPT_NAME']).'/';
                } else {
                    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
                    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                        $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
                    }
                }
            }
        }
        $_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);

        define('INSTALL_VERSION', $this->state->getInstallVersion());
        // 1.4
        define('INSTALL_PATH', realpath($this->latestRootDir.DIRECTORY_SEPARATOR.'install'));
        // 1.5 ...
        define('_PS_INSTALL_PATH_', INSTALL_PATH.DIRECTORY_SEPARATOR);
        // 1.6
        if (!defined('_PS_CORE_DIR_')) {
            define('_PS_CORE_DIR_', _PS_ROOT_DIR_);
        }


        define('PS_INSTALLATION_IN_PROGRESS', true);
        define('SETTINGS_FILE_PHP', $this->prodRootDir . '/app/config/parameters.php');
        define('SETTINGS_FILE_YML', $this->prodRootDir . '/app/config/parameters.yml');
        define('DEFINES_FILE', $this->prodRootDir .'/config/defines.inc.php');
        define('INSTALLER__PS_BASE_URI', substr($_SERVER['REQUEST_URI'], 0, -1 * (strlen($_SERVER['REQUEST_URI']) - strrpos($_SERVER['REQUEST_URI'], '/')) - strlen(substr(dirname($_SERVER['REQUEST_URI']), strrpos(dirname($_SERVER['REQUEST_URI']), '/')+1))));
        //	define('INSTALLER__PS_BASE_URI_ABSOLUTE', 'http://'.ToolsInstall::getHttpHost(false, true).INSTALLER__PS_BASE_URI);

        $filePrefix = 'PREFIX_';
        $engineType = 'ENGINE_TYPE';

        $mysqlEngine = (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM');

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set('Europe/Paris');
        }

        // if _PS_ROOT_DIR_ is defined, use it instead of "guessing" the module dir.
        if (defined('_PS_ROOT_DIR_') and !defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', _PS_ROOT_DIR_.'/modules/');
        } elseif (!defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', INSTALL_PATH.'/../modules/');
        }

        $upgrade_dir_php = 'upgrade/php';
        if (!file_exists(INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php)) {
            $upgrade_dir_php = 'php';
            if (!file_exists(INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php)) {
                $this->next = 'error';
                $this->next_desc = $this->trans('/install/upgrade/php directory is missing in archive or directory', array(), 'Modules.Autoupgrade.Admin');
                $this->nextQuickInfo[] = $this->trans('/install/upgrade/php directory is missing in archive or directory', array(), 'Modules.Autoupgrade.Admin');
                $this->nextErrors[] = $this->trans('/install/upgrade/php directory is missing in archive or directory.', array(), 'Modules.Autoupgrade.Admin');
                return false;
            }
        }
        define('_PS_INSTALLER_PHP_UPGRADE_DIR_',  INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php.DIRECTORY_SEPARATOR);

        //old version detection
        global $oldversion, $logger;
        $oldversion = false;

        if (!file_exists(SETTINGS_FILE_PHP)) {
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans('The app/config/parameters.php file was not found.', array(), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('The app/config/parameters.php file was not found.', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }
        if (!file_exists(SETTINGS_FILE_YML)) {
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans('The app/config/parameters.yml file was not found.', array(), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('The app/config/parameters.yml file was not found.', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }

        $oldversion = Configuration::get('PS_VERSION_DB');

        if (!defined('__PS_BASE_URI__')) {
            define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])).'/../../');
        }

        if (!defined('_THEMES_DIR_')) {
            define('_THEMES_DIR_', __PS_BASE_URI__.'themes/');
        }

        $versionCompare =  version_compare(INSTALL_VERSION, $oldversion);

        if ($versionCompare == '-1') {
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans(
                'Current version: %oldversion%. Version to install: %newversion%.',
                array(
                    '%oldversion%' => $oldversion,
                    '%newversion%' => INSTALL_VERSION,
                ),
                'Modules.Autoupgrade.Admin'
            );
            $this->nextErrors[] = $this->trans(
                'Current version: %oldversion%. Version to install: %newversion%',
                array(
                    '%oldversion%' => $oldversion,
                    '%newversion%' => INSTALL_VERSION,
                ),
                'Modules.Autoupgrade.Admin'
            );
            $this->nextQuickInfo[] = $this->trans('[ERROR] Version to install is too old.', array(), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('[ERROR] Version to install is too old.', array(), 'Modules.Autoupgrade.Admin');
            return false;
        } elseif ($versionCompare == 0) {
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans('You already have the %s version.', array(INSTALL_VERSION), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('You already have the %s version.', array(INSTALL_VERSION), 'Modules.Autoupgrade.Admin');
            return false;
        } elseif ($versionCompare === false) {
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans('There is no older version. Did you delete or rename the app/config/parameters.php file?', array(), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('There is no older version. Did you delete or rename the app/config/parameters.php file?', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }

        //check DB access
        $this->db;
        error_reporting(E_ALL);
        $resultDB = Db::checkConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
        if ($resultDB !== 0) {
            // $logger->logError('Invalid database configuration.');
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans('Invalid database configuration', array(), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('Invalid database configuration', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }

        //custom sql file creation
        $upgradeFiles = array();

        $upgrade_dir_sql = INSTALL_PATH.'/upgrade/sql';
        // if 1.4;
        if (!file_exists($upgrade_dir_sql)) {
            $upgrade_dir_sql = INSTALL_PATH.'/sql/upgrade';
        }

        if (!file_exists($upgrade_dir_sql)) {
            $this->next = 'error';
            $this->next_desc = $this->trans('Unable to find upgrade directory in the installation path.', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }

        if ($handle = opendir($upgrade_dir_sql)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' and $file != '..') {
                    $upgradeFiles[] = str_replace(".sql", "", $file);
                }
            }
            closedir($handle);
        }
        if (empty($upgradeFiles)) {
            $this->next = 'error';
            $this->nextQuickInfo[] = $this->trans('Cannot find the SQL upgrade files. Please check that the %s folder is not empty.', array($upgrade_dir_sql), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('Cannot find the SQL upgrade files. Please check that the %s folder is not empty.', array($upgrade_dir_sql), 'Modules.Autoupgrade.Admin');
            // fail 31
            return false;
        }
        natcasesort($upgradeFiles);
        $neededUpgradeFiles = array();

        $arrayVersion = explode('.', $oldversion);
        $versionNumbers = count($arrayVersion);
        if ($versionNumbers != 4) {
            $arrayVersion = array_pad($arrayVersion, 4, '0');
        }

        $oldversion = implode('.', $arrayVersion);

        foreach ($upgradeFiles as $version) {
            if (version_compare($version, $oldversion) == 1 && version_compare(INSTALL_VERSION, $version) != -1) {
                $neededUpgradeFiles[] = $version;
            }
        }

        if (strpos(INSTALL_VERSION, '.') === false) {
            $this->nextQuickInfo[] = $this->trans('%s is not a valid version number.', array(INSTALL_VERSION), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('%s is not a valid version number.', array(INSTALL_VERSION), 'Modules.Autoupgrade.Admin');
            return false;
        }

        $sqlContentVersion = array();
        if ($this->deactivateCustomModule) {
            $this->getModuleAdapter()->disableNonNativeModules();
        }

        foreach ($neededUpgradeFiles as $version) {
            $file = $upgrade_dir_sql.DIRECTORY_SEPARATOR.$version.'.sql';
            if (!file_exists($file)) {
                $this->next = 'error';
                $this->nextQuickInfo[] = $this->trans('Error while loading SQL upgrade file "%s.sql".', array($version), 'Modules.Autoupgrade.Admin');
                $this->nextErrors[] = $this->trans('Error while loading SQL upgrade file "%s.sql".', array($version), 'Modules.Autoupgrade.Admin');
                return false;
                $logger->logError('Error while loading SQL upgrade file.');
            }
            if (!$sqlContent = file_get_contents($file)."\n") {
                $this->next = 'error';
                $this->nextQuickInfo[] = $this->trans('Error while loading SQL upgrade file %s.', array($version), 'Modules.Autoupgrade.Admin');
                $this->nextErrors[] = $this->trans('Error while loading sql SQL file %s.', array($version), 'Modules.Autoupgrade.Admin');
                return false;
                $logger->logError(sprintf('Error while loading sql upgrade file %s.', $version));
            }
            $sqlContent = str_replace(array($filePrefix, $engineType), array(_DB_PREFIX_, $mysqlEngine), $sqlContent);
            $sqlContent = preg_split("/;\s*[\r\n]+/", $sqlContent);
            $sqlContentVersion[$version] = $sqlContent;
        }

        //sql file execution
        global $requests, $warningExist;
        $requests = '';
        $warningExist = false;

        $request = '';

        foreach ($sqlContentVersion as $upgrade_file => $sqlContent) {
            foreach ($sqlContent as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    /* If php code have to be executed */
                    if (strpos($query, '/* PHP:') !== false) {
                        /* Parsing php code */
                        $pos = strpos($query, '/* PHP:') + strlen('/* PHP:');
                        $phpString = substr($query, $pos, strlen($query) - $pos - strlen(' */;'));
                        $php = explode('::', $phpString);
                        preg_match('/\((.*)\)/', $phpString, $pattern);
                        $paramsString = trim($pattern[0], '()');
                        preg_match_all('/([^,]+),? ?/', $paramsString, $parameters);
                        if (isset($parameters[1])) {
                            $parameters = $parameters[1];
                        } else {
                            $parameters = array();
                        }
                        if (is_array($parameters)) {
                            foreach ($parameters as &$parameter) {
                                $parameter = str_replace('\'', '', $parameter);
                            }
                        }

                        // reset phpRes to a null value
                        $phpRes = null;
                        /* Call a simple function */
                        if (strpos($phpString, '::') === false) {
                            $func_name = str_replace($pattern[0], '', $php[0]);
                            if (version_compare(INSTALL_VERSION, '1.5.5.0', '=') && $func_name == 'fix_download_product_feature_active') {
                                continue;
                            }

                            if (!file_exists(_PS_INSTALLER_PHP_UPGRADE_DIR_.strtolower($func_name).'.php')) {
                                $this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] '.$upgrade_file.' PHP - missing file '.$query.'</div>';
                                $this->nextErrors[] = '[ERROR] '.$upgrade_file.' PHP - missing file '.$query;
                                $warningExist = true;
                            } else {
                                require_once(_PS_INSTALLER_PHP_UPGRADE_DIR_.strtolower($func_name).'.php');
                                $phpRes = call_user_func_array($func_name, $parameters);
                            }
                        }
                        /* Or an object method */
                        else {
                            $func_name = array($php[0], str_replace($pattern[0], '', $php[1]));
                            $this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] '.$upgrade_file.' PHP - Object Method call is forbidden ( '.$php[0].'::'.str_replace($pattern[0], '', $php[1]).')</div>';
                            $this->nextErrors[] = '[ERROR] '.$upgrade_file.' PHP - Object Method call is forbidden ('.$php[0].'::'.str_replace($pattern[0], '', $php[1]).')';
                            $warningExist = true;
                        }

                        if (isset($phpRes) && (is_array($phpRes) && !empty($phpRes['error'])) || $phpRes === false) {
                            // $this->next = 'error';
                            $this->nextQuickInfo[] = '
								<div class="upgradeDbError">
									[ERROR] PHP '.$upgrade_file.' '.$query."\n".'
									'.(empty($phpRes['error']) ? '' : $phpRes['error']."\n").'
									'.(empty($phpRes['msg']) ? '' : ' - '.$phpRes['msg']."\n").'
								</div>';
                            $this->nextErrors[] = '
								[ERROR] PHP '.$upgrade_file.' '.$query."\n".'
								'.(empty($phpRes['error']) ? '' : $phpRes['error']."\n").'
								'.(empty($phpRes['msg']) ? '' : ' - '.$phpRes['msg']."\n");
                            $warningExist = true;
                        } else {
                            $this->nextQuickInfo[] = '<div class="upgradeDbOk">[OK] PHP '.$upgrade_file.' : '.$query.'</div>';
                        }
                        if (isset($phpRes)) {
                            unset($phpRes);
                        }
                    } else {
                        if (strstr($query, 'CREATE TABLE') !== false) {
                            $pattern = '/CREATE TABLE.*[`]*'._DB_PREFIX_.'([^`]*)[`]*\s\(/';
                            preg_match($pattern, $query, $matches);
                            ;
                            if (isset($matches[1]) && $matches[1]) {
                                $drop = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$matches[1].'`;';
                                $result = $this->db->execute($drop, false);
                                if ($result) {
                                    $this->nextQuickInfo[] = '<div class="upgradeDbOk">'.$this->trans('[DROP] SQL %s table has been dropped.', array('`'._DB_PREFIX_.$matches[1].'`'), 'Modules.Autoupgrade.Admin').'</div>';
                                }
                            }
                        }
                        $result = $this->db->execute($query, false);
                        if (!$result) {
                            $error = $this->db->getMsgError();
                            $error_number = $this->db->getNumberError();
                            $this->nextQuickInfo[] = '
								<div class="upgradeDbError">
								[WARNING] SQL '.$upgrade_file.'
								'.$error_number.' in '.$query.': '.$error.'</div>';

                            $duplicates = array('1050', '1054', '1060', '1061', '1062', '1091');
                            if (!in_array($error_number, $duplicates)) {
                                $this->nextErrors[] = 'SQL '.$upgrade_file.' '.$error_number.' in '.$query.': '.$error;
                                $warningExist = true;
                            }
                        } else {
                            $this->nextQuickInfo[] = '<div class="upgradeDbOk">[OK] SQL '.$upgrade_file.' '.$query.'</div>';
                        }
                    }
                    if (isset($query)) {
                        unset($query);
                    }
                }
            }
        }
        if ($this->next == 'error') {
            $this->next_desc = $this->trans('An error happened during the database upgrade.', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }

        if (version_compare(INSTALL_VERSION, '1.7.1.1', '>=')) {
            $schemaUpgrade = new \PrestaShopBundle\Service\Database\Upgrade();
            $outputCommand = 'prestashop:schema:update-without-foreign';
        } else {
            $schemaUpgrade = new \PrestaShopBundle\Service\Cache\Refresh();
            $outputCommand = 'doctrine:schema:update';
        }

        $schemaUpgrade->addDoctrineSchemaUpdate();
        $output = $schemaUpgrade->execute();

        if (0 !== $output[$outputCommand]['exitCode']) {
            $msgErrors = explode("\n", $output[$outputCommand]['output']);
            $this->nextErrors[] = $this->trans('Error upgrading Doctrine schema', array(), 'Modules.Autoupgrade.Admin');
            $this->nextQuickInfo[] = $msgErrors;
            $this->next_desc = $msgErrors;
            return false;
        }

        $this->nextQuickInfo[] = $this->trans('Database upgrade OK', array(), 'Modules.Autoupgrade.Admin'); // no error!

        // Settings updated, compile and cache directories must be emptied
        $arrayToClean[] = $this->prodRootDir.'/app/cache/';
        $arrayToClean[] = $this->prodRootDir.'/cache/smarty/cache/';
        $arrayToClean[] = $this->prodRootDir.'/cache/smarty/compile/';

        foreach ($arrayToClean as $dir) {
            if (!file_exists($dir)) {
                $this->nextQuickInfo[] = $this->trans('[SKIP] directory "%s" does not exist and cannot be emptied.', array(str_replace($this->prodRootDir, '', $dir)), 'Modules.Autoupgrade.Admin');
                continue;
            } else {
                foreach (scandir($dir) as $file) {
                    if ($file[0] != '.' && $file != 'index.php' && $file != '.htaccess') {
                        if (is_file($dir.$file)) {
                            unlink($dir.$file);
                        } elseif (is_dir($dir.$file.DIRECTORY_SEPARATOR)) {
                            self::deleteDirectory($dir.$file.DIRECTORY_SEPARATOR);
                        }
                        $this->nextQuickInfo[] = $this->trans('[CLEANING CACHE] File %s removed', array($file), 'Modules.Autoupgrade.Admin');
                    }
                }
            }
        }

        $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET `name` = \'PS_LEGACY_IMAGES\' WHERE name LIKE \'0\' AND `value` = 1');
        $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET `value` = 0 WHERE `name` LIKE \'PS_LEGACY_IMAGES\'');
        if ($this->db->getValue('SELECT COUNT(id_product_download) FROM `'._DB_PREFIX_.'product_download` WHERE `active` = 1') > 0) {
            $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET `value` = 1 WHERE `name` LIKE \'PS_VIRTUAL_PROD_FEATURE_ACTIVE\'');
        }

        if (defined('_THEME_NAME_') && $this->updateDefaultTheme && 'classic' === _THEME_NAME_) {
            $separator = addslashes(DIRECTORY_SEPARATOR);
            $file = _PS_ROOT_DIR_.$separator.'themes'.$separator._THEME_NAME_.$separator.'cache'.$separator;
            if (file_exists($file)) {
                foreach (scandir($file) as $cache) {
                    if ($cache[0] != '.' && $cache != 'index.php' && $cache != '.htaccess' && file_exists($file.$cache) && !is_dir($file.$cache)) {
                        if (file_exists($dir.$cache)) {
                            unlink($file.$cache);
                        }
                    }
                }
            }
        }

        // Upgrade languages
        if (!defined('_PS_TOOL_DIR_')) {
            define('_PS_TOOL_DIR_', _PS_ROOT_DIR_.'/tools/');
        }
        if (!defined('_PS_TRANSLATIONS_DIR_')) {
            define('_PS_TRANSLATIONS_DIR_', _PS_ROOT_DIR_.'/translations/');
        }
        if (!defined('_PS_MODULES_DIR_')) {
            define('_PS_MODULES_DIR_', _PS_ROOT_DIR_.'/modules/');
        }
        if (!defined('_PS_MAILS_DIR_')) {
            define('_PS_MAILS_DIR_', _PS_ROOT_DIR_.'/mails/');
        }

        $langs = $this->db->executeS('SELECT * FROM `'._DB_PREFIX_.'lang` WHERE `active` = 1');

        if (is_array($langs)) {
            foreach ($langs as $lang) {
                $isoCode = $lang['iso_code'];

                if (Validate::isLangIsoCode($isoCode)) {
                    $errorsLanguage = array();

                    Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage);

                    $lang_pack = Language::getLangDetails($isoCode);
                    Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

                    if (!$this->keepMails) {
                        Language::installEmailsLanguagePack($lang_pack, $errorsLanguage);
                    }

                    if (empty($errorsLanguage)) {
                        Language::loadLanguages();

                        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated

                        $cldrUpdate = new \PrestaShop\PrestaShop\Core\Cldr\Update(_PS_TRANSLATIONS_DIR_);
                        $cldrUpdate->fetchLocale(Language::getLocaleByIso($isoCode));
                    } else {
                        $this->nextErrors[] = $this->trans('Error updating translations', array(), 'Modules.Autoupgrade.Admin');
                        $this->nextQuickInfo[] = $this->trans('Error updating translations', array(), 'Modules.Autoupgrade.Admin');
                        $this->next_desc = $this->trans('Error updating translations', array(), 'Modules.Autoupgrade.Admin');
                        return false;
                    }
                }
            }
        }

        require_once(_PS_ROOT_DIR_.'/src/Core/Foundation/Database/EntityInterface.php');

        if (file_exists(_PS_ROOT_DIR_.'/classes/Tools.php')) {
            require_once(_PS_ROOT_DIR_.'/classes/Tools.php');
        }
        if (!class_exists('Tools2', false) and class_exists('ToolsCore')) {
            eval('class Tools2 extends ToolsCore{}');
        }

        if (class_exists('Tools2') && method_exists('Tools2', 'generateHtaccess')) {
            $url_rewrite = (bool)$this->db->getvalue('SELECT `value` FROM `'._DB_PREFIX_.'configuration` WHERE name=\'PS_REWRITING_SETTINGS\'');

            if (!defined('_MEDIA_SERVER_1_')) {
                define('_MEDIA_SERVER_1_', '');
            }

            if (!defined('_PS_USE_SQL_SLAVE_')) {
                define('_PS_USE_SQL_SLAVE_', false);
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/ObjectModel.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/ObjectModel.php');
            }
            if (!class_exists('ObjectModel', false) and class_exists('ObjectModelCore')) {
                eval('abstract class ObjectModel extends ObjectModelCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Configuration.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Configuration.php');
            }
            if (!class_exists('Configuration', false) and class_exists('ConfigurationCore')) {
                eval('class Configuration extends ConfigurationCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/cache/Cache.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/cache/Cache.php');
            }
            if (!class_exists('Cache', false) and class_exists('CacheCore')) {
                eval('abstract class Cache extends CacheCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/PrestaShopCollection.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/PrestaShopCollection.php');
            }
            if (!class_exists('PrestaShopCollection', false) and class_exists('PrestaShopCollectionCore')) {
                eval('class PrestaShopCollection extends PrestaShopCollectionCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/shop/ShopUrl.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/shop/ShopUrl.php');
            }
            if (!class_exists('ShopUrl', false) and class_exists('ShopUrlCore')) {
                eval('class ShopUrl extends ShopUrlCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/shop/Shop.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/shop/Shop.php');
            }
            if (!class_exists('Shop', false) and class_exists('ShopCore')) {
                eval('class Shop extends ShopCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Translate.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Translate.php');
            }
            if (!class_exists('Translate', false) and class_exists('TranslateCore')) {
                eval('class Translate extends TranslateCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/module/Module.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/module/Module.php');
            }
            if (!class_exists('Module', false) and class_exists('ModuleCore')) {
                eval('class Module extends ModuleCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Validate.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Validate.php');
            }
            if (!class_exists('Validate', false) and class_exists('ValidateCore')) {
                eval('class Validate extends ValidateCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Language.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Language.php');
            }
            if (!class_exists('Language', false) and class_exists('LanguageCore')) {
                eval('class Language extends LanguageCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Tab.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Tab.php');
            }
            if (!class_exists('Tab', false) and class_exists('TabCore')) {
                eval('class Tab extends TabCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Dispatcher.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Dispatcher.php');
            }
            if (!class_exists('Dispatcher', false) and class_exists('DispatcherCore')) {
                eval('class Dispatcher extends DispatcherCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Hook.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Hook.php');
            }
            if (!class_exists('Hook', false) and class_exists('HookCore')) {
                eval('class Hook extends HookCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Context.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Context.php');
            }
            if (!class_exists('Context', false) and class_exists('ContextCore')) {
                eval('class Context extends ContextCore{}');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/Group.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/Group.php');
            }
            if (!class_exists('Group', false) and class_exists('GroupCore')) {
                eval('class Group extends GroupCore{}');
            }

            Tools2::generateHtaccess(null, $url_rewrite);
        }

        $path = $this->adminDir.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'header.tpl';
        if (file_exists($path)) {
            unlink($path);
        }

        if (file_exists(_PS_ROOT_DIR_.'/app/cache/dev/class_index.php')) {
            unlink(_PS_ROOT_DIR_.'/app/cache/dev/class_index.php');
        }
        if (file_exists(_PS_ROOT_DIR_.'/app/cache/prod/class_index.php')) {
            unlink(_PS_ROOT_DIR_.'/app/cache/prod/class_index.php');
        }

        // Clear XML files
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/blog-fr.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/blog-fr.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/default_country_modules_list.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/default_country_modules_list.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/modules_list.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/modules_list.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/modules_native_addons.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/modules_native_addons.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/must_have_modules_list.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/must_have_modules_list.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/tab_modules_list.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/tab_modules_list.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/trusted_modules_list.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/trusted_modules_list.xml');
        }
        if (file_exists(_PS_ROOT_DIR_.'/config/xml/untrusted_modules_list.xml')) {
            unlink(_PS_ROOT_DIR_.'/config/xml/untrusted_modules_list.xml');
        }

        if ($this->deactivateCustomModule) {
            $exist = $this->db->getValue('SELECT `id_configuration` FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE \'PS_DISABLE_OVERRIDES\'');
            if ($exist) {
                $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET value = 1 WHERE `name` LIKE \'PS_DISABLE_OVERRIDES\'');
            } else {
                $this->db->execute('INSERT INTO `'._DB_PREFIX_.'configuration` (name, value, date_add, date_upd) VALUES ("PS_DISABLE_OVERRIDES", 1, NOW(), NOW())');
            }

            if (file_exists(_PS_ROOT_DIR_.'/classes/PrestaShopAutoload.php')) {
                require_once(_PS_ROOT_DIR_.'/classes/PrestaShopAutoload.php');
            }

            if (class_exists('PrestaShopAutoload') && method_exists('PrestaShopAutoload', 'generateIndex')) {
                PrestaShopAutoload::getInstance()->_include_override_path = false;
                PrestaShopAutoload::getInstance()->generateIndex();
            }
        }

        $themeName = ($this->changeToDefaultTheme ? 'classic' : _THEME_NAME_);
        $themeErrors = (new ThemeAdapter($this->db))->enableTheme($themeName);

        if ($themeErrors !== true) {
            $this->nextQuickInfo[] = $themeErrors;
            $this->nextErrors[] = $themeErrors;
            $this->next_desc = $themeErrors;
            return false;
        }

        Tools::clearCache();

        // delete cache filesystem if activated
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            $depth = (int)$this->db->getValue('SELECT value
				FROM '._DB_PREFIX_.'configuration
				WHERE name = "PS_CACHEFS_DIRECTORY_DEPTH"');
            if ($depth) {
                if (!defined('_PS_CACHEFS_DIRECTORY_')) {
                    define('_PS_CACHEFS_DIRECTORY_', $this->prodRootDir.'/cache/cachefs/');
                }
                self::deleteDirectory(_PS_CACHEFS_DIRECTORY_, false);
                if (class_exists('CacheFs', false)) {
                    self::createCacheFsDirectories((int)$depth);
                }
            }
        }

        $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET value="0" WHERE name = "PS_HIDE_OPTIMIZATION_TIS"', false);
        $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET value="1" WHERE name = "PS_NEED_REBUILD_INDEX"', false);
        $this->db->execute('UPDATE `'._DB_PREFIX_.'configuration` SET value="'.INSTALL_VERSION.'" WHERE name = "PS_VERSION_DB"', false);

        if ($warningExist) {
            $this->state->setWarningExists(true);
            $this->nextQuickInfo[] = $this->trans('Warning detected during upgrade.', array(), 'Modules.Autoupgrade.Admin');
            $this->nextErrors[] = $this->trans('Warning detected during upgrade.', array(), 'Modules.Autoupgrade.Admin');
            $this->next_desc = $this->trans('Warning detected during upgrade.', array(), 'Modules.Autoupgrade.Admin');
        } else {
            $this->next_desc = $this->trans('Database upgrade completed', array(), 'Modules.Autoupgrade.Admin');
        }

        return true;
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
            $this->nextQuickInfo[] = $this->trans('%s ignored', array($file), 'Modules.Autoupgrade.Admin');
            return true;
        } else {
            if (is_dir($orig)) {
                // if $dest is not a directory (that can happen), just remove that file
                if (!is_dir($dest) and file_exists($dest)) {
                    unlink($dest);
                    $this->nextQuickInfo[] = $this->trans('[WARNING] File %1$s has been deleted.', array($file), 'Modules.Autoupgrade.Admin');
                }
                if (!file_exists($dest)) {
                    if (mkdir($dest)) {
                        $this->nextQuickInfo[] = $this->trans('Directory %1$s created.', array($file), 'Modules.Autoupgrade.Admin');
                        return true;
                    } else {
                        $this->next = 'error';
                        $this->nextQuickInfo[] = $this->trans('Error while creating directory %s.', array($dest), 'Modules.Autoupgrade.Admin');
                        $this->nextErrors[] = $this->next_desc = $this->trans('Error while creating directory %s.', array($dest), 'Modules.Autoupgrade.Admin');
                        return false;
                    }
                } else { // directory already exists
                    $this->nextQuickInfo[] = $this->trans('Directory %s already exists.', array($file), 'Modules.Autoupgrade.Admin');
                    return true;
                }
            } elseif (is_file($orig)) {
                $translationAdapter = $this->getTranslationAdapter();
                if ($translationAdapter->isTranslationFile($file) && file_exists($dest)) {
                    $type_trad = $translationAdapter->getTranslationFileType($file);
                    if ($translationAdapter->mergeTranslationFile($orig, $dest, $type_trad)) {
                        $this->nextQuickInfo[] = $this->trans('[TRANSLATION] The translation files have been merged into file %s.', array($dest), 'Modules.Autoupgrade.Admin');
                        return true;
                    }
                    $this->nextQuickInfo[] = $this->nextErrors[] = $this->trans(
                        '[TRANSLATION] The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                        array('%filename%' => $dest),
                        'Modules.Autoupgrade.Admin'
                    );
                }

                // upgrade exception were above. This part now process all files that have to be upgraded (means to modify or to remove)
                // delete before updating (and this will also remove deprecated files)
                if (copy($orig, $dest)) {
                    $this->nextQuickInfo[] = $this->trans('Copied %1$s.', array($file), 'Modules.Autoupgrade.Admin');
                    return true;
                } else {
                    $this->next = 'error';
                    $this->nextQuickInfo[] = $this->trans('Error while copying file %s', array($file), 'Modules.Autoupgrade.Admin');
                    $this->nextErrors[] = $this->next_desc = $this->trans('Error while copying file %s', array($file), 'Modules.Autoupgrade.Admin');
                    return false;
                }
            } elseif (is_file($dest)) {
                if (file_exists($dest)) {
                    unlink($dest);
                }
                $this->nextQuickInfo[] = sprintf('removed file %1$s.', $file);
                return true;
            } elseif (is_dir($dest)) {
                if (strpos($dest, DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR) === false) {
                    self::deleteDirectory($dest, true);
                }
                $this->nextQuickInfo[] = sprintf('removed dir %1$s.', $file);
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
        $response = new AjaxResponse($this->getTranslator(), $this->state);
        return $response->setError($this->error)
            ->setStepDone($this->stepDone)
            ->setNext($this->next)
            ->setNextDesc($this->next_desc)
            ->setNextParams($this->nextParams)
            ->setNextQuickInfo($this->nextQuickInfo)
            ->setNextErrors($this->nextErrors)
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
        $this->nextQuickInfo[] = $e->getQuickInfos();
        if ($e->getSeverity() === UpgradeException::SEVERITY_ERROR) {
            $this->next = 'error';
            $this->error = true;
            $this->nextErrors[] = $e->getMessage();
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
        return new Translation($this->getTranslator(), $this->state->getInstalledLanguagesIso());
    }

    public function getTranslator()
    {
        return new \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator($this);
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

        $this->zipAction = new ZipAction($this->getTranslator(), $this->prodRootDir);
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
