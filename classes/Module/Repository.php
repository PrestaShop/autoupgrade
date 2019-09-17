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

namespace PrestaShop\Module\AutoUpgrade\Module;

use PrestaShop\Module\AutoUpgrade\Addons\ClientInterface;
use PrestaShop\Module\AutoUpgrade\Exception\AutoupgradeException;

/**
 * Class Repository is used to get lists of modules (those on the disk, native modules by version, ...)
 */
class Repository
{
    /** @var string */
    private $modulesDir;

    /** @var string */
    private $disabledModulesDir;

    /** @var ClientInterface */
    private $addonsClient;

    /** @var array */
    private $modulesByVersion;

    /**
     * @param string $modulesDir
     * @param string $disabledModulesDir
     * @param ClientInterface $addonsClient
     */
    public function __construct(
        $modulesDir,
        $disabledModulesDir,
        ClientInterface $addonsClient
    ) {
        $this->modulesDir = rtrim($modulesDir, '/') . '/';
        $this->disabledModulesDir = rtrim($disabledModulesDir, '/') . '/';
        $this->addonsClient = $addonsClient;
        $this->modulesByVersion = [];
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
            $this->modulesByVersion[$version] = $this->addonsClient->request('native', ['version' => $version]);
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
        $nativeModules = [];
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
     * @param string $modulesDirectory
     *
     * @return array
     */
    private function listModulesFromDirectory($modulesDirectory)
    {
        $modulesInDirectory = [];
        $modules = scandir($modulesDirectory);
        foreach ($modules as $name) {
            if (in_array($name, ['.', '..', 'index.php', '.htaccess'])
                || !@is_dir($modulesDirectory . $name) && @file_exists($modulesDirectory . $name . DIRECTORY_SEPARATOR . $name . '.php')) {
                continue;
            }

            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
                throw new AutoupgradeException('Invalid module name ' . $name);
            }
            $modulesInDirectory[] = $name;
        }

        return $modulesInDirectory;
    }
}
