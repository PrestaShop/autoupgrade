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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
 * update configuration after validating the new values.
 */
class UpdateConfig extends AbstractTask
{
    /**
     * Data being passed by CLI entry point
     *
     * @var array
     */
    protected $cliParameters;

    public function run()
    {
        // nothing next
        $this->next = '';

        // Was coming from AdminSelfUpgrade::currentParams before
        $configurationData = $this->getConfigurationData();
        $config = [];
        // update channel
        if (isset($configurationData['channel'])) {
            $config['channel'] = $configurationData['channel'];
            $config['archive.filename'] = Upgrader::DEFAULT_FILENAME;
            // Switch on default theme if major upgrade (i.e: 1.6 -> 1.7)
            $config['PS_AUTOUP_CHANGE_DEFAULT_THEME'] = ($configurationData['channel'] === 'major');
        }
        if (isset($configurationData['private_release_link'], $configurationData['private_release_md5'])) {
            $config['channel'] = 'private';
            $config['private_release_link'] = $configurationData['private_release_link'];
            $config['private_release_md5'] = $configurationData['private_release_md5'];
            $config['private_allow_major'] = $configurationData['private_allow_major'];
        }
        // if (!empty($request['archive_name']) && !empty($request['archive_num']))
        if (!empty($configurationData['archive_prestashop'])) {
            $file = $configurationData['archive_prestashop'];
            if (!file_exists($this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file)) {
                $this->error = true;
                $this->logger->info($this->translator->trans('File %s does not exist. Unable to select that channel.', [$file], 'Modules.Autoupgrade.Admin'));

                return false;
            }
            if (empty($configurationData['archive_num'])) {
                $this->error = true;
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.', [], 'Modules.Autoupgrade.Admin'));

                return false;
            }
            $xmlFile = $configurationData['archive_xml'];
            if (!empty($xmlFile) && !file_exists($this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $xmlFile)) {
                $this->error = true;
                $this->logger->info($this->translator->trans('File %s does not exist. Unable to select that channel', [$xmlFile], 'Modules.Autoupgrade.Admin'));

                return false;
            }
            $config['channel'] = 'archive';
            $config['archive.filename'] = $configurationData['archive_prestashop'];
            $config['archive.version_num'] = $configurationData['archive_num'];
            $config['archive.xml'] = $configurationData['archive_xml'];
            // $config['archive_name'] = $request['archive_name'];
            $this->logger->info($this->translator->trans('Upgrade process will use archive.', [], 'Modules.Autoupgrade.Admin'));
        }
        if (isset($configurationData['directory_num'])) {
            $config['channel'] = 'directory';
            if (empty($configurationData['directory_num']) || strpos($configurationData['directory_num'], '.') === false) {
                $this->error = true;
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.', [], 'Modules.Autoupgrade.Admin'));

                return false;
            }

            $config['directory.version_num'] = $configurationData['directory_num'];
        }
        if (isset($configurationData['skip_backup'])) {
            $config['skip_backup'] = $configurationData['skip_backup'];
        }
        if (isset($configurationData['PS_AUTOUP_CHANGE_DEFAULT_THEME'])) {
            $config['PS_AUTOUP_CHANGE_DEFAULT_THEME'] = $configurationData['PS_AUTOUP_CHANGE_DEFAULT_THEME'];
        }

        if (!$this->writeConfig($config)) {
            $this->error = true;
            $this->logger->info($this->translator->trans('Error on saving configuration', [], 'Modules.Autoupgrade.Admin'));
        }
    }

    public function inputCliParameters($parameters)
    {
        $this->cliParameters = $parameters;
    }

    protected function getConfigurationData()
    {
        if (null !== $this->cliParameters) {
            return $this->getCLIParams();
        }

        return $this->getRequestParams();
    }

    protected function getCLIParams()
    {
        if (empty($this->cliParameters)) {
            throw new \RuntimeException('Empty CLI parameters - did CLI entry point failed to provide data?');
        }

        return $this->cliParameters;
    }

    protected function getRequestParams()
    {
        return empty($_REQUEST['params']) ? [] : $_REQUEST['params'];
    }

    /**
     * update module configuration (saved in file UpgradeFiles::configFilename) with $new_config.
     *
     * @param array $config
     *
     * @return bool true if success
     */
    private function writeConfig($config)
    {
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::CONFIG_FILENAME) && !empty($config['channel'])) {
            $this->container->getUpgrader()->channel = $config['channel'];
            $this->container->getUpgrader()->checkPSVersion();

            $this->container->getState()->setInstallVersion($this->container->getUpgrader()->version_num);
        }

        $this->container->getUpgradeConfiguration()->merge($config);
        $this->logger->info($this->translator->trans('Configuration successfully updated.', [], 'Modules.Autoupgrade.Admin') . ' <strong>' . $this->translator->trans('This page will now be reloaded and the module will check if a new version is available.', [], 'Modules.Autoupgrade.Admin') . '</strong>');

        return (new UpgradeConfigurationStorage($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR))->save($this->container->getUpgradeConfiguration(), UpgradeFileNames::CONFIG_FILENAME);
    }
}
