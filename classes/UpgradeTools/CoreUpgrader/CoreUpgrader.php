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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use Cache;
use Exception;
use InvalidArgumentException;
use Language;
use ParseError;
use PrestaShop\Module\AutoUpgrade\Database\MysqlErrorCode;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\ThemeAdapter;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Theme\Command\AdaptThemeToRTLLanguagesCommand;
use PrestaShop\PrestaShop\Core\Domain\Theme\ValueObject\ThemeName;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\Localization\RTL\Processor as RtlStylesheetProcessor;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class used to modify the core of PrestaShop, on the files are copied on the filesystem.
 * It will run subtasks such as database upgrade, language upgrade etc.
 */
abstract class CoreUpgrader
{
    /**
     * @var UpgradeContainer
     */
    protected $container;

    /**
     * @var \Db
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Version PrestaShop is upgraded to.
     *
     * @var string
     */
    protected $destinationUpgradeVersion;

    /**
     * Path to the temporary install folder, where upgrade files can be found
     *
     * @var string
     */
    protected $pathToInstallFolder;

    /**
     * Path to the folder containing PHP upgrade files
     *
     * @var string
     */
    protected $pathToUpgradeScripts;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct(UpgradeContainer $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->fileSystem = $this->container->getFileSystem();
        $this->logger = $logger;
        $this->destinationUpgradeVersion = $this->container->getUpdateState()->getDestinationVersion();
        $this->pathToInstallFolder = realpath($this->container->getProperty(UpgradeContainer::TMP_FILES_PATH) . DIRECTORY_SEPARATOR . 'install');
        $this->pathToUpgradeScripts = dirname(__DIR__, 3) . '/upgrade/';
        if ($this->fileSystem->exists($this->pathToInstallFolder . DIRECTORY_SEPARATOR . 'autoload.php')) {
            require_once $this->pathToInstallFolder . DIRECTORY_SEPARATOR . 'autoload.php';
        }
        $this->db = \Db::getInstance();
    }

    /**
     * @throws UpgradeException
     * @throws Exception
     */
    public function setupUpdateEnvironment(): void
    {
        $this->initConstants();
        error_reporting(E_ALL);
    }

    /**
     * @throws UpgradeException
     */
    public function finalizeCoreUpdate(): void
    {
        $this->logger->info($this->container->getTranslator()->trans('Running generic queries'));
        $this->runRecurrentQueries();

        $this->logger->info($this->container->getTranslator()->trans('Database update OK')); // no error!

        $this->installAssets();

        $this->logger->info($this->container->getTranslator()->trans('Updating languages'));
        $this->upgradeLanguages();

        $this->logger->info($this->container->getTranslator()->trans('Regenerating htaccess'));
        $this->generateHtaccess();

        $this->logger->info($this->container->getTranslator()->trans('Cleaning XML files'));
        $this->cleanXmlFiles();

        if (UpgradeConfiguration::isOverrideAllowed()) {
            $this->logger->info($this->container->getTranslator()->trans('Keeping overrides in place'));
        } else {
            $this->logger->info($this->container->getTranslator()->trans('Disabling overrides'));
            $this->disableOverrides();
        }

        $this->updateTheme();

        $this->runCoreCacheClean();

        if ($this->container->getUpdateState()->isWarningDetected()) {
            $this->logger->warning($this->container->getTranslator()->trans('Warning detected during update.'));
        } else {
            $this->logger->info($this->container->getTranslator()->trans('Database update completed'));
        }
    }

    /**
     * @throws Exception
     */
    protected function initConstants(): void
    {
        // Initialize
        // setting the memory limit to 128M only if current is lower
        $memory_limit = ini_get('memory_limit');
        if ((substr($memory_limit, -1) != 'G')
            && ((substr($memory_limit, -1) == 'M' && substr($memory_limit, 0, -1) < 512)
                || is_numeric($memory_limit) && ((int) $memory_limit < 131072))
        ) {
            @ini_set('memory_limit', '512M');
        }

        // Redefine REQUEST_URI if empty (on some webservers...)
        if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) {
            if (!isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['SCRIPT_FILENAME'])) {
                $_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_FILENAME'];
            }
            if (isset($_SERVER['SCRIPT_NAME'])) {
                if (basename($_SERVER['SCRIPT_NAME']) == 'index.php' && empty($_SERVER['QUERY_STRING'])) {
                    $_SERVER['REQUEST_URI'] = dirname($_SERVER['SCRIPT_NAME']) . '/';
                } else {
                    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
                    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                    }
                }
            }
        }
        $_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);

        // Kept for backward compatbility (unknown consequences on old versions of PrestaShop)
        if (!defined('INSTALL_VERSION')) {
            define('INSTALL_VERSION', $this->destinationUpgradeVersion);
        }
        // 1.4
        if (!defined('INSTALL_PATH')) {
            define('INSTALL_PATH', $this->pathToInstallFolder);
        }
        // 1.5 ...
        if (!defined('_PS_CORE_DIR_')) {
            define('_PS_CORE_DIR_', _PS_ROOT_DIR_);
        }
        if (!defined('PS_INSTALLATION_IN_PROGRESS')) {
            define('PS_INSTALLATION_IN_PROGRESS', true);
        }
        if (!defined('SETTINGS_FILE_PHP')) {
            define('SETTINGS_FILE_PHP', $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/app/config/parameters.php');
        }
        if (!defined('SETTINGS_FILE_YML')) {
            define('SETTINGS_FILE_YML', $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/app/config/parameters.yml');
        }
        if (!defined('DEFINES_FILE')) {
            define('DEFINES_FILE', $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/config/defines.inc.php');
        }
        if (!defined('INSTALLER__PS_BASE_URI')) {
            define('INSTALLER__PS_BASE_URI', substr($_SERVER['REQUEST_URI'], 0, -1 * (strlen($_SERVER['REQUEST_URI']) - strrpos($_SERVER['REQUEST_URI'], '/')) - strlen(substr(dirname($_SERVER['REQUEST_URI']), strrpos(dirname($_SERVER['REQUEST_URI']), '/') + 1))));
        }
        if (!defined('_PS_INSTALL_PATH_')) {
            define('_PS_INSTALL_PATH_', $this->pathToInstallFolder . '/');
        }
        if (!defined('_PS_INSTALL_DATA_PATH_')) {
            define('_PS_INSTALL_DATA_PATH_', _PS_INSTALL_PATH_ . 'data/');
        }
        if (!defined('_PS_INSTALL_CONTROLLERS_PATH_')) {
            define('_PS_INSTALL_CONTROLLERS_PATH_', _PS_INSTALL_PATH_ . 'controllers/');
        }
        if (!defined('_PS_INSTALL_MODELS_PATH_')) {
            define('_PS_INSTALL_MODELS_PATH_', _PS_INSTALL_PATH_ . 'models/');
        }
        if (!defined('_PS_INSTALL_LANGS_PATH_')) {
            define('_PS_INSTALL_LANGS_PATH_', _PS_INSTALL_PATH_ . 'langs/');
        }
        if (!defined('_PS_INSTALL_FIXTURES_PATH_')) {
            define('_PS_INSTALL_FIXTURES_PATH_', _PS_INSTALL_PATH_ . 'fixtures/');
        }
        if (!defined('_PS_INSTALLER_PHP_UPGRADE_DIR_')) {
            define('_PS_INSTALLER_PHP_UPGRADE_DIR_', $this->pathToUpgradeScripts . 'php/');
        }

        // if _PS_ROOT_DIR_ is defined, use it instead of "guessing" the module dir.
        if (defined('_PS_ROOT_DIR_') && !defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', _PS_ROOT_DIR_ . '/modules/');
        } elseif (!defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', $this->pathToInstallFolder . '/../modules/');
        }

        if (!defined('__PS_BASE_URI__')) {
            define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])) . '/../../');
        }

        if (!defined('_THEMES_DIR_')) {
            define('_THEMES_DIR_', __PS_BASE_URI__ . 'themes/');
        }
    }

    /**
     * Ask the core to disable the modules not coming from PrestaShop.
     *
     * @throws Exception
     */
    public function disableCustomModules(): void
    {
        $this->container->getModuleAdapter()->disableNonNativeModules($this->pathToUpgradeScripts);
    }

    /**
     * @throws UpgradeException
     *
     * @return array<string, string>
     */
    protected function getUpgradeSqlFilesListToApply(string $upgrade_dir_sql, string $oldversion): array
    {
        if (!$this->fileSystem->exists($upgrade_dir_sql)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Unable to find upgrade directory in the installation path.'));
        }

        $upgradeFiles = $neededUpgradeFiles = [];
        if ($handle = opendir($upgrade_dir_sql)) {
            while (false !== ($file = readdir($handle))) {
                if ($file[0] === '.') {
                    continue;
                }
                if (!is_readable($upgrade_dir_sql . $file)) {
                    throw new UpgradeException($this->container->getTranslator()->trans('Error while loading SQL upgrade file "%s".', [$file]));
                }
                $upgradeFiles[] = str_replace('.sql', '', $file);
            }
            closedir($handle);
        }
        if (empty($upgradeFiles)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Cannot find the SQL upgrade files. Please check that the %s folder is not empty.', [$upgrade_dir_sql]));
        }
        natcasesort($upgradeFiles);

        foreach ($upgradeFiles as $version) {
            if (version_compare($version, $oldversion) == 1 && version_compare($this->destinationUpgradeVersion, $version) != -1) {
                $neededUpgradeFiles[$version] = $upgrade_dir_sql . $version . '.sql';
            }
        }

        return $neededUpgradeFiles;
    }

    /**
     * @throws UpgradeException
     *
     * @return array<array{'version':string,'query':string}>
     */
    public function getSqlContentList(string $originVersion): array
    {
        $upgrade_dir_sql = $this->pathToUpgradeScripts . '/sql/';

        return $this->applySqlParams(
            $this->getUpgradeSqlFilesListToApply($upgrade_dir_sql, $originVersion)
        );
    }

    /**
     * Replace some placeholders in the SQL upgrade files (prefix, engine...).
     *
     * @param array<string, string> $sqlFiles
     *
     * @return array<array{'version':string,'query':string}> of SQL requests per version
     */
    protected function applySqlParams(array $sqlFiles): array
    {
        $search = ['PREFIX_', 'ENGINE_TYPE', 'DB_NAME'];
        $replace = [_DB_PREFIX_, (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM'), _DB_NAME_];

        $sqlRequests = [];

        foreach ($sqlFiles as $version => $file) {
            $sqlContent = file_get_contents($file) . "\n";
            $sqlContent = str_replace($search, $replace, $sqlContent);
            $sqlContent = array_filter(preg_split("/;\s*[\r\n]+/", $sqlContent));
            foreach ($sqlContent as $query) {
                $sqlRequests[] = [
                    'version' => $version,
                    'query' => $query,
                ];
            }
        }

        return $sqlRequests;
    }

    /**
     * ToDo, check to move this in a database class.
     *
     * @param string $upgrade_file File in which the request is stored (for logs)
     * @param string $query
     */
    public function runQuery(string $upgrade_file, string $query): void
    {
        $query = trim($query);
        if (empty($query)) {
            return;
        }
        // If php code have to be executed
        if (strpos($query, '/* PHP:') !== false) {
            $this->runPhpQuery($upgrade_file, $query);

            return;
        }
        $this->runSqlQuery($upgrade_file, $query);
    }

    /**
     * @param string $query
     *
     * @return false|string
     */
    private function extractPhpStringFromQuery(string $query)
    {
        $pos = strpos($query, '/* PHP:') + strlen('/* PHP:');

        return substr($query, $pos, strlen($query) - $pos - strlen(' */;'));
    }

    /**
     * @param string $phpString
     *
     * @return string
     */
    private function extractParametersAsString(string $phpString): string
    {
        preg_match('/\((.*)\)/', $phpString, $pattern);

        return $pattern[0];
    }

    /**
     * @param string $paramsString
     *
     * @return array<mixed>
     */
    private function extractParametersFromString(string $paramsString): array
    {
        $paramsString = trim($paramsString, '()');
        $code = "return [$paramsString];";

        try {
            $parameters = eval($code);
        } catch (ParseError $e) {
            throw new InvalidArgumentException('Error while parsing the parameter string.');
        }

        return $parameters;
    }

    protected function runPhpQuery(string $upgrade_file, string $query): void
    {
        // Parsing php code
        $phpString = $this->extractPhpStringFromQuery($query);
        $php = explode('::', $phpString);
        $stringParameters = $this->extractParametersAsString($phpString);
        $parameters = $this->extractParametersFromString($stringParameters);

        // reset phpRes to a null value
        $phpRes = null;
        // Call a simple function
        if (strpos($phpString, '::') === false) {
            $func_name = str_replace($stringParameters, '', $php[0]);
            $pathToPhpDirectory = $this->pathToUpgradeScripts . 'php/';

            if (!$this->fileSystem->exists($pathToPhpDirectory . strtolower($func_name) . '.php')) {
                $this->logMissingFileError($pathToPhpDirectory, $func_name, $query);

                return;
            }

            require_once $pathToPhpDirectory . strtolower($func_name) . '.php';
            try {
                $phpRes = call_user_func_array($func_name, $parameters);
            } catch (UpdateDatabaseException $exception) {
                $this->logPhpError($upgrade_file, $query, $exception->getMessage());
            }
        }
        // Or an object method
        else {
            $this->logForbiddenObjectMethodError($phpString, $upgrade_file);

            return;
        }

        if ($this->hasPhpError($phpRes)) {
            $message = (empty($phpRes['error']) ? '' : $phpRes['error'] . "\n") .
                (empty($phpRes['msg']) ? '' : ' - ' . $phpRes['msg'] . "\n");
            $this->logPhpError($upgrade_file, $query, $message);
        } else {
            $this->logger->debug($this->container->getTranslator()->trans('Migration file: %s, Query: %s', [$upgrade_file, $query]));
        }
    }

    /**
     * @param mixed $phpRes
     *
     * @return bool
     */
    private function hasPhpError($phpRes): bool
    {
        return isset($phpRes) && (is_array($phpRes) && !empty($phpRes['error'])) || $phpRes === false;
    }

    private function logPhpError(string $upgrade_file, string $query, string $message): void
    {
        $this->logger->warning('PHP ' . $upgrade_file . ' ' . $query . ": \n" . $message);
        $this->container->getUpdateState()->setWarningDetected(true);
    }

    private function logMissingFileError(string $path, string $func_name, string $query): void
    {
        $this->logger->warning($this->container->getTranslator()->trans('%s PHP - missing file %s', [$path . strtolower($func_name), $query]));
        $this->container->getUpdateState()->setWarningDetected(true);
    }

    private function logForbiddenObjectMethodError(string $phpString, string $upgrade_file): void
    {
        $this->logger->warning($this->container->getTranslator()->trans('%s PHP - Object Method call is forbidden (%s)', [$upgrade_file, $phpString]));
        $this->container->getUpdateState()->setWarningDetected(true);
    }

    protected function runSqlQuery(string $upgrade_file, string $query): void
    {
        if (strstr($query, 'CREATE TABLE') !== false) {
            $pattern = '/CREATE TABLE.*[`]*' . _DB_PREFIX_ . '([^`]*)[`]*\s\(/';
            preg_match($pattern, $query, $matches);
            if (!empty($matches[1])) {
                $drop = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $matches[1] . '`;';
                if ($this->db->execute($drop, false)) {
                    $this->logger->debug($this->container->getTranslator()->trans('SQL %s table has been dropped.', ['`' . _DB_PREFIX_ . $matches[1] . '`']));
                }
            }
        }

        if ($this->db->execute($query, false)) {
            $this->logger->debug($this->container->getTranslator()->trans('Migration file: %s, Query: %s', [$upgrade_file, $query]));

            return;
        }

        $error = $this->db->getMsgError();
        $error_number = $this->db->getNumberError();

        $duplicates = [
            MysqlErrorCode::TABLE_ALREADY_EXISTS,
            MysqlErrorCode::UNKNOWN_COLUMN_IN_FIELD_LIST,
            MysqlErrorCode::DUPLICATE_COLUMN_NAME,
            MysqlErrorCode::DUPLICATE_KEY,
            MysqlErrorCode::DUPLICATE_ENTRY,
            MysqlErrorCode::CANNOT_DROP_KEY,
        ];
        if (!in_array($error_number, $duplicates)) {
            $this->logger->warning($this->container->getTranslator()->trans('The execution of the query failed: %s, %s', [$error_number, $error]));
            $this->logger->warning($this->container->getTranslator()->trans('Migration file: %s, Query: %s', [$upgrade_file, $query]));
            $this->container->getUpdateState()->setWarningDetected(true);
        } else {
            $this->logger->debug($this->container->getTranslator()->trans('The execution of the query failed: %s, %s. This error code can be safely ignored.', [$error_number, $error]));
            $this->logger->debug($this->container->getTranslator()->trans('Migration file: %s, Query: %s', [$upgrade_file, $query]));
        }
    }

    public function writeNewSettings(): void
    {
        // Do nothing
    }

    protected function runRecurrentQueries(): void
    {
        $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'configuration` SET `name` = \'PS_LEGACY_IMAGES\' WHERE name LIKE \'0\' AND `value` = 1');
        $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'configuration` SET `value` = 0 WHERE `name` LIKE \'PS_LEGACY_IMAGES\'');
        if ($this->db->getValue('SELECT COUNT(id_product_download) FROM `' . _DB_PREFIX_ . 'product_download` WHERE `active` = 1') > 0) {
            $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'configuration` SET `value` = 1 WHERE `name` LIKE \'PS_VIRTUAL_PROD_FEATURE_ACTIVE\'');
        }

        // Exported from the end of doUpgrade()
        $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'configuration` SET value="0" WHERE name = "PS_HIDE_OPTIMIZATION_TIS"', false);
        $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'configuration` SET value="1" WHERE name = "PS_NEED_REBUILD_INDEX"', false);
        $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'configuration` SET value="' . $this->destinationUpgradeVersion . '" WHERE name = "PS_VERSION_DB"', false);
    }

    protected function upgradeLanguages(): void
    {
        if (!defined('_PS_TOOL_DIR_')) {
            define('_PS_TOOL_DIR_', _PS_ROOT_DIR_ . '/tools/');
        }
        if (!defined('_PS_TRANSLATIONS_DIR_')) {
            define('_PS_TRANSLATIONS_DIR_', _PS_ROOT_DIR_ . '/translations/');
        }
        if (!defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', _PS_ROOT_DIR_ . '/modules/');
        }
        if (!defined('_PS_MAILS_DIR_')) {
            define('_PS_MAILS_DIR_', _PS_ROOT_DIR_ . '/mails/');
        }

        $langs = $this->db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'lang` WHERE `active` = 1');

        if (!is_array($langs)) {
            return;
        }
        foreach ($langs as $lang) {
            $this->upgradeLanguage($lang);
        }
    }

    /**
     * @param array<string, mixed> $lang
     */
    abstract protected function upgradeLanguage($lang): void;

    protected function generateHtaccess(): void
    {
        $this->loadEntityInterface();

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Tools.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Tools.php';
        }

        // @phpstan-ignore function.alreadyNarrowedType (Some PrestaShop may not have this method)
        if (!class_exists('ToolsCore') || !method_exists('ToolsCore', 'generateHtaccess')) {
            return;
        }
        $url_rewrite = (bool) $this->db->getvalue('SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration` WHERE name=\'PS_REWRITING_SETTINGS\'');

        if (!defined('_MEDIA_SERVER_1_')) {
            define('_MEDIA_SERVER_1_', '');
        }

        if (!defined('_PS_USE_SQL_SLAVE_')) {
            define('_PS_USE_SQL_SLAVE_', false);
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/ObjectModel.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/ObjectModel.php';
        }
        if (!class_exists('ObjectModel', false) && class_exists('ObjectModelCore')) {
            eval('abstract class ObjectModel extends ObjectModelCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Configuration.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Configuration.php';
        }
        if (!class_exists('Configuration', false) && class_exists('ConfigurationCore')) {
            eval('class Configuration extends ConfigurationCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/cache/Cache.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/cache/Cache.php';
        }
        if (!class_exists('Cache', false) && class_exists('CacheCore')) {
            eval('abstract class Cache extends CacheCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/PrestaShopCollection.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/PrestaShopCollection.php';
        }
        if (!class_exists('PrestaShopCollection', false) && class_exists('PrestaShopCollectionCore')) {
            eval('class PrestaShopCollection extends PrestaShopCollectionCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/shop/ShopUrl.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/shop/ShopUrl.php';
        }
        if (!class_exists('ShopUrl', false) && class_exists('ShopUrlCore')) {
            eval('class ShopUrl extends ShopUrlCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/shop/Shop.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/shop/Shop.php';
        }
        if (!class_exists('Shop', false) && class_exists('ShopCore')) {
            eval('class Shop extends ShopCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Translate.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Translate.php';
        }
        if (!class_exists('Translate', false) && class_exists('TranslateCore')) {
            eval('class Translate extends TranslateCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/module/Module.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/module/Module.php';
        }
        if (!class_exists('Module', false) && class_exists('ModuleCore')) {
            eval('class Module extends ModuleCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Validate.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Validate.php';
        }
        if (!class_exists('Validate', false) && class_exists('ValidateCore')) {
            eval('class Validate extends ValidateCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Language.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Language.php';
        }
        if (!class_exists('Language', false) && class_exists('LanguageCore')) {
            eval('class Language extends LanguageCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Tab.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Tab.php';
        }
        if (!class_exists('Tab', false) && class_exists('TabCore')) {
            eval('class Tab extends TabCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Dispatcher.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Dispatcher.php';
        }
        if (!class_exists('Dispatcher', false) && class_exists('DispatcherCore')) {
            eval('class Dispatcher extends DispatcherCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Hook.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Hook.php';
        }
        if (!class_exists('Hook', false) && class_exists('HookCore')) {
            eval('class Hook extends HookCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Context.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Context.php';
        }
        if (!class_exists('Context', false) && class_exists('ContextCore')) {
            eval('class Context extends ContextCore{}');
        }

        if ($this->fileSystem->exists(_PS_ROOT_DIR_ . '/classes/Group.php')) {
            require_once _PS_ROOT_DIR_ . '/classes/Group.php';
        }
        if (!class_exists('Group', false) && class_exists('GroupCore')) {
            eval('class Group extends GroupCore{}');
        }

        \ToolsCore::generateHtaccess(null, $url_rewrite);
    }

    protected function loadEntityInterface(): void
    {
        require_once _PS_ROOT_DIR_ . '/src/Core/Foundation/Database/EntityInterface.php';
    }

    /**
     * @throws Exception
     */
    protected function cleanXmlFiles(): void
    {
        $files = [
            $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'header.tpl',
            _PS_ROOT_DIR_ . '/config/xml/blog-fr.xml',
            _PS_ROOT_DIR_ . '/config/xml/default_country_modules_list.xml',
            _PS_ROOT_DIR_ . '/config/xml/modules_list.xml',
            _PS_ROOT_DIR_ . '/config/xml/modules_native_addons.xml',
            _PS_ROOT_DIR_ . '/config/xml/must_have_modules_list.xml',
            _PS_ROOT_DIR_ . '/config/xml/tab_modules_list.xml',
            _PS_ROOT_DIR_ . '/config/xml/trusted_modules_list.xml',
            _PS_ROOT_DIR_ . '/config/xml/untrusted_modules_list.xml',
        ];
        foreach ($files as $path) {
            if ($this->fileSystem->exists($path)) {
                $this->fileSystem->remove($path);
            }
        }
    }

    protected function disableOverrides(): void
    {
        if (class_exists('PrestaShopAutoload') && method_exists('PrestaShopAutoload', 'generateIndex')) {
            \PrestaShopAutoload::getInstance()->_include_override_path = false;
            \PrestaShopAutoload::getInstance()->generateIndex();
        }
    }

    /**
     * @throws UpgradeException
     * @throws Exception
     */
    protected function updateTheme(): void
    {
        $this->updateRTLFiles();
        $this->switchToDefaultTheme();
    }

    /**
     * @throws UpgradeException
     * @throws \Exception
     */
    protected function switchToDefaultTheme(): void
    {
        // The merchant can ask for keeping its current theme.
        if (!$this->container->getUpdateConfiguration()->shouldSwitchToDefaultTheme()) {
            $this->logger->info($this->container->getTranslator()->trans('Keeping current theme'));

            return;
        }

        $this->logger->info($this->container->getTranslator()->trans('Switching to default theme.'));
        $themeAdapter = new ThemeAdapter($this->db);

        Cache::clean('*');

        $themeErrors = $themeAdapter->enableTheme(
            $themeAdapter->getDefaultTheme()
        );

        if ($themeErrors !== true) {
            throw new UpgradeException($themeErrors);
        }
    }

    protected function updateRTLFiles(): void
    {
        if (!$this->container->shouldUpdateRTLFiles()) {
            $this->logger->info($this->container->getTranslator()->trans('No RTL language detected, skipping RTL file update.'));

            return;
        }

        // BO theme
        if (class_exists(RtlStylesheetProcessor::class)) {
            $this->logger->info($this->container->getTranslator()->trans('Update the RTL files of back-office themes.'));

            $this->removeExistingRTLFiles([
                ['directory' => $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH) . DIRECTORY_SEPARATOR . 'themes'],
            ]);

            (new RtlStylesheetProcessor(
                $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH),
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'themes',
                []
            ))
                ->setProcessBOTheme(true)
                ->setProcessDefaultModules(true)
                ->process();
        }

        // FO themes
        if (!class_exists(AdaptThemeToRTLLanguagesCommand::class)) {
            return;
        }

        $this->logger->info($this->container->getTranslator()->trans('Update the RTL files of front-office themes.'));
        $themeAdapter = new ThemeAdapter($this->db);

        $themes = $themeAdapter->getListFromDisk();
        $this->removeExistingRTLFiles($themes);

        foreach ($themes as $theme) {
            $adaptThemeToTRLLanguages = new AdaptThemeToRTLLanguagesCommand(
                new ThemeName($theme['name'])
            );

            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->container->getModuleAdapter()->getCommandBus();

            try {
                $commandBus->handle($adaptThemeToTRLLanguages);
            } catch (CoreException $e) {
                $this->logger->warning($this->container->getTranslator()->trans('PHP Impossible to generate RTL files for theme %s: %s', [$theme['name'], $e->getMessage()]));

                $this->container->getUpdateState()->setWarningDetected(true);
            }
        }
    }

    /**
     * @param array{array{'directory':string}} $themes
     */
    private function removeExistingRTLFiles(array $themes): void
    {
        foreach ($themes as $theme) {
            $files = $this->container->getFilesystemAdapter()->listSampleFiles($theme['directory'], '_rtl.css');
            $this->fileSystem->remove($files);
        }
    }

    /**
     * @throws Exception
     */
    protected function runCoreCacheClean(): void
    {
        $this->logger->info($this->container->getTranslator()->trans('Cleaning file cache'));
        $this->container->getCacheCleaner()->cleanFolders();
        $this->logger->info($this->container->getTranslator()->trans('Running opcache_reset'));
        $this->container->resetOpcache();
    }

    /**
     * @throws Exception
     */
    public function shouldWarmupCoreCache(): bool
    {
        $destinationVersion = $this->container->getUpgrader()->getDestinationVersion();

        return version_compare($destinationVersion, '9.0.0') >= 0 && php_sapi_name() === 'cli';
    }

    /**
     * @throws Exception
     */
    public function warmupCoreCache(): void
    {
        $args = 'cache:warmup --no-interaction --no-optional-warmers --env=prod';
        $result = $this->container->getCoreConsoleExecutable()->callCommand($args);

        if ($result['returnCode'] !== 0) {
            throw new UpgradeException($this->container->getTranslator()->trans("An error was raised when warming up the core cache: \n %s", [implode("\n", $result['output'])]));
        }
        $this->logger->debug($this->container->getTranslator()->trans('Core cache has been generated to avoid dependency conflicts.'));
    }

    /**
     * PrestaShop 9.0.1 adds an helper function that runs "assets:install".
     * It install some bundles assets via symlink or hard copy if symlink aren't possible in this environment.
     */
    private function installAssets(): void
    {
        if (!class_exists('PrestaShop\PrestaShop\Adapter\Bundle\AssetsInstaller')) {
            // Nothing to do if the class does not exist in the version we update to.
            return;
        }

        $this->logger->info($this->container->getTranslator()->trans('Installing assets'));

        // Calling PrestaShop\PrestaShop\Adapter\Bundle\AssetsInstaller::installAssets() is impossible at the time of writing of this method.
        // Attempting to call it from Update Assistant v7 triggers a collision between the versions of the package symfony/console provided
        // by the core and Update Assistant. We run a basic exec to avoid it.

        $adminSubDir = $this->container->getProperty(UpgradeContainer::PS_ADMIN_SUBDIR);
        $args = 'assets:install --symlink --no-interaction --env=prod ' . $adminSubDir;
        $result = $this->container->getCoreConsoleExecutable()->callCommand($args);

        if ($result['returnCode'] !== 0) {
            throw new UpgradeException($this->container->getTranslator()->trans("A code %d was returned while installing assets: \n %s", [$result['returnCode'], implode("\n", $result['output'])]));
        }
    }
}
