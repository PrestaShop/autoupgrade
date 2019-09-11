<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Configuration;
use PrestaShop\Module\AutoUpgrade\AutoupgradeException;
use PrestaShop\Module\AutoUpgrade\Tools14;

class ModuleRepository
{
    /** @var string */
    private $modulesDir;

    /** @var string */
    private $disabledModulesDir;

    /** @var bool */
    private $isAddonsUp = true;

    /** @var array */
    private $modulesByVersion;

    /**
     * @param string $modulesDir
     * @param string $disabledModulesDir
     */
    public function __construct($modulesDir, $disabledModulesDir)
    {
        $this->modulesDir = rtrim($modulesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->disabledModulesDir = rtrim($disabledModulesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->modulesByVersion = array();
    }

    /**
     * Returns the list of modules present in the modules folder, the array returned
     * contains the technical names of the modules.
     *
     * @return array
     */
    public function getModulesOnDisk()
    {
        return $this->listModulesFromDirectory($this->modulesDir);
    }

    /**
     * Returns the list of modules present in the disabled modules folder, the array returned
     * contains the technical names of the modules.
     *
     * @return array
     */
    public function getDisabledModulesOnDisk()
    {
        return $this->listModulesFromDirectory($this->disabledModulesDir);
    }

    /**
     * Returns the list of native modules for the specified version, the array returned
     * contains the technical names of modules indexed by their Addons id.
     *
     * @param string $version
     *
     * @return array|false
     */
    public function getNativeModulesForVersion($version)
    {
        if (empty($this->modulesByVersion[$version])) {
            $this->modulesByVersion[$version] = $this->getModulesFromAddons('native', ['version' => $version]);
        }

        return $this->modulesByVersion[$version];
    }

    /**
     * Return the list of custom modules on disk (a custom module is any module that is
     * not native). The array returned contains the technical names of the modules.
     *
     * @param array $versions
     *
     * @return array
     */
    public function getCustomModulesOnDisk($versions)
    {
        $modulesOnDisk = $this->getModulesOnDisk();
        $nativeModules = array();
        foreach ($versions as $version) {
            $nativeModules = array_merge(
                $nativeModules,
                $this->getNativeModulesForVersion($version)
            );
        }

        $customModules = [];
        foreach ($modulesOnDisk as $moduleName) {
            if (!in_array($moduleName, $nativeModules)) {
                $customModules[] = $moduleName;
            }
        }

        return $customModules;
    }

    /**
     * @param string $request
     * @param array $params
     *
     * @return array|false
     */
    private function getModulesFromAddons($request, array $params)
    {
        $requestContent = $this->requestAddons($request, $params);
        if (empty($requestContent)) {
            false;
        }

        $modules = @simplexml_load_string($requestContent);
        if (!$modules || !count($modules->module)) {
            return false;
        }

        $addonsModules = [];
        foreach ($modules->module as $module) {
            $addonsModules[(int) $module->id] = (string) $module->name;
        }

        return $addonsModules;
    }

    /**
     * @param string $request
     * @param array $params
     *
     * @return string|false
     */
    private function requestAddons($request, array $params)
    {
        if (!$this->isAddonsUp) {
            return false;
        }

        $post_query_data = array(
            'version' => isset($params['version']) ? $params['version'] : _PS_VERSION_,
            'iso_code' => Tools14::strtolower(isset($params['iso_country']) ? $params['iso_country'] : 'all'),
            'format' => isset($params['format']) ? $params['format'] : 'xml',
        );
        if (isset($params['source'])) {
            $post_query_data['source'] = $params['source'];
        }

        $post_data = http_build_query($post_query_data);

        $end_point = 'api.addons.prestashop.com';

        switch ($request) {
            case 'native':
                $post_data .= '&method=listing&action=native';

                break;
            case 'partner':
                $post_data .= '&method=listing&action=partner';

                break;
            case 'service':
                $post_data .= '&method=listing&action=service';

                break;
            case 'native_all':
                $post_data .= '&method=listing&action=native&iso_code=all';

                break;
            case 'must-have':
                $post_data .= '&method=listing&action=must-have';

                break;
            case 'must-have-themes':
                $post_data .= '&method=listing&action=must-have-themes';

                break;
            case 'check_customer':
                $post_data .= '&method=check_customer&username=' . urlencode($params['username_addons']) . '&password=' . urlencode($params['password_addons']);

                break;
            case 'check_module':
                $post_data .= '&method=check&module_name=' . urlencode($params['module_name']) . '&module_key=' . urlencode($params['module_key']);

                break;
            case 'module':
                $post_data .= '&method=module&id_module=' . urlencode($params['id_module']);
                if (isset($params['username_addons'], $params['password_addons'])) {
                    $post_data .= '&username=' . urlencode($params['username_addons']) . '&password=' . urlencode($params['password_addons']);
                }

                break;
            case 'hosted_module':
                $post_data .= '&method=module&id_module=' . urlencode((int) $params['id_module']) . '&username=' . urlencode($params['hosted_email'])
                    . '&password=' . urlencode($params['password_addons'])
                    . '&shop_url=' . urlencode(isset($params['shop_url']) ? $params['shop_url'] : Tools14::getShopDomain())
                    . '&mail=' . urlencode(isset($params['email']) ? $params['email'] : Configuration::get('PS_SHOP_EMAIL'));

                break;
            case 'install-modules':
                $post_data .= '&method=listing&action=install-modules';
                $post_data .= defined('_PS_HOST_MODE_') ? '-od' : '';

                break;
            default:
                return false;
        }

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $post_data,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 5,
            ),
        ));

        if ($content = Tools14::file_get_contents('https://' . $end_point, false, $context)) {
            return $content;
        }

        if ($content = Tools14::file_get_contents('http://' . $end_point, false, $context)) {
            return $content;
        }

        $this->isAddonsUp = false;

        return false;
    }

    /**
     * @param string $modulesDirectory
     *
     * @return array
     */
    private function listModulesFromDirectory($modulesDirectory)
    {
        $modulesInDirectory = array();
        $modules = scandir($modulesDirectory);
        foreach ($modules as $name) {
            if (!in_array($name, array('.', '..', 'index.php', '.htaccess')) && @is_dir($modulesDirectory . $name) && @file_exists($modulesDirectory . $name . DIRECTORY_SEPARATOR . $name . '.php')) {
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
                    throw new AutoupgradeException('Invalid module name ' . $name);
                }
                $modulesInDirectory[] = $name;
            }
        }

        return $modulesInDirectory;
    }
}
