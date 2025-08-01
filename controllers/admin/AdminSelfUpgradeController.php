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

use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Router\Router;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\Request;

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
    private $autoupgradePath;

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

        if (defined('_PS_ADMIN_DIR_')) {
            // Check that the Update assistant working directory is existing or create it
            if (!file_exists($this->autoupgradePath) && !@mkdir($this->autoupgradePath)) {
                $this->_errors[] = $this->trans('Unable to create the directory "%s"', [$this->autoupgradePath]);

                return;
            }

            // Make sure that the Update assistant working directory is writeable
            if (!is_writable($this->autoupgradePath)) {
                $this->_errors[] = $this->trans('Unable to write in the directory "%s"', [$this->autoupgradePath]);

                return;
            }

            $file_tab = @filemtime($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
            $file = @filemtime(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->autoupgradeDir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');

            if ($file_tab < $file) {
                // If a previous version of ajax-upgradetab.php exists, delete it
                if (file_exists($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php')) {
                    @unlink($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
                }
                // copy new version
                @copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->autoupgradeDir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php',
                    $this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
                // adjust file modification time
                @touch($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php', $file);
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
        $this->autoupgradePath = $this->adminDir . DIRECTORY_SEPARATOR . $this->autoupgradeDir;
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
        $moduleDir = $this->upgradeContainer->getProperty(UpgradeContainer::WORKSPACE_PATH);
        $this->upgradeContainer->getWorkspace()->init($moduleDir);

        $this->upgradeContainer->getBackupState()->importFromArray(
            empty($_REQUEST['params']) ? [] : $_REQUEST['params']
        );
        $this->upgradeContainer->getRestoreState()->importFromArray(
            empty($_REQUEST['params']) ? [] : $_REQUEST['params']
        );
        $this->upgradeContainer->getUpdateState()->importFromArray(
            empty($_REQUEST['params']) ? [] : $_REQUEST['params']
        );

        $this->upgradeContainer->getFileStorage()->cleanAllUpdateFiles();
        $this->upgradeContainer->getFileStorage()->cleanAllBackupFiles();
        $this->upgradeContainer->getFileStorage()->cleanAllRestoreFiles();

        // If you have defined this somewhere, you know what you do
        // load options from configuration if we're not in ajax mode
        if (!$this->ajax) {
            $upgrader = $this->upgradeContainer->getUpgrader();
            $this->upgradeContainer->getCookie()->create(
                $this->context->employee->id,
                $this->context->language->iso_code
            );
        }
    }

    public function postProcess()
    {
        if (!$this->isActualPHPVersionCompatible) {
            return true;
        }

        parent::postProcess();

        return true;
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

        $this->content = $this->upgradeContainer->getTwig()->render('@ModuleAutoUpgrade/module-script-variables.html.twig', [
            'autoupgrade_variables' => $this->getScriptsVariables(),
        ]);
        $request = Request::createFromGlobals();
        $this->addUIAssets($request);

        $response = (new Router($this->upgradeContainer))->handle($request);

        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $response->send();
            exit;
        }

        $this->content .= $response;

        return parent::initContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function getScriptsVariables()
    {
        $adminDir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
        $currentPsVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);
        $currentMajorVersion = VersionUtils::splitPrestaShopVersion($currentPsVersion)['major'];

        return [
            'token' => $this->token,
            'admin_url' => __PS_BASE_URI__ . $adminDir,
            'admin_dir' => $adminDir,
            'stepper_parent_id' => \PrestaShop\Module\AutoUpgrade\Twig\PageSelectors::STEPPER_PARENT_ID,
            'module_version' => $this->module->version,
            'php_version' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
            'anonymous_id' => $this->upgradeContainer->getProperty(UpgradeContainer::ANONYMOUS_USER_ID),
            'ps_version' => $currentPsVersion,
            'bo_language' => $this->context->language->locale,
            'bo_timezone' => date_default_timezone_get(),
            'links' => [
                'help' => DocumentationLinks::getDevDocUpdateAssistantWebUrl($currentMajorVersion),
            ],
            'translations' => [
                'success' => $this->trans('SUCCESS'),
                'failed' => $this->trans('FAILED'),
            ],
        ];
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    private function addUIAssets(Request $request)
    {
        $assetsEnvironment = $this->upgradeContainer->getAssetsEnvironment();
        $assetsBaseUrl = $assetsEnvironment->getAssetsBaseUrl($request);
        $twig = $this->upgradeContainer->getTwig();

        if ($assetsEnvironment->isDevMode()) {
            $this->context->controller->addCSS($assetsBaseUrl . '/src/scss/appUI/main.scss');
            $this->content .= $twig->render('@ModuleAutoUpgrade/module-script-tag.html.twig', ['module_type' => true, 'src' => $assetsBaseUrl . '/src/ts/appUI/main.ts']);
        } else {
            $this->context->controller->addCSS($assetsBaseUrl . '/css/autoupgrade.css');
            $this->content .= $twig->render('@ModuleAutoUpgrade/module-script-tag.html.twig', ['module_type' => true, 'src' => $assetsBaseUrl . '/js/autoupgrade.js?v=' . $this->module->version]);
        }
    }
}
