<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
 * update configuration after validating the new values.
 */
class UpdateConfig extends AbstractTask
{
    /**
     * @var bool
     */
    protected $isRunInCLI = false;

    /**
     * Data being passed by CLI entry point
     *
     * @var array
     */
    protected $cliParameters = [];

    public function run()
    {
        // nothing next
        $this->next = '';

        // Was coming from AdminSelfUpgrade::currentParams before
        $input = $this->fetchConfigurationData();

        $config = array();
        // update channel
        if (isset($input['channel'])) {
            $config['channel'] = $input['channel'];
            $config['archive.filename'] = Upgrader::DEFAULT_FILENAME;
            // Switch on default theme if major upgrade (i.e: 1.6 -> 1.7)
            $config['PS_AUTOUP_CHANGE_DEFAULT_THEME'] = ($input['channel'] === 'major');
        }
        if (isset($input['private_release_link'], $input['private_release_md5'])) {
            $config['channel'] = 'private';
            $config['private_release_link'] = $input['private_release_link'];
            $config['private_release_md5'] = $input['private_release_md5'];
            $config['private_allow_major'] = $input['private_allow_major'];
        }
        // if (!empty($input['archive_name']) && !empty($input['archive_num']))
        if (!empty($input['archive_prestashop'])) {
            $file = $input['archive_prestashop'];
            if (!file_exists($this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file)) {
                $this->error = true;
                $this->logger->info($this->translator->trans('File %s does not exist. Unable to select that channel.', array($file), 'Modules.Autoupgrade.Admin'));

                return false;
            }
            if (empty($input['archive_num'])) {
                $this->error = true;
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.', array(), 'Modules.Autoupgrade.Admin'));

                return false;
            }
            $config['channel'] = 'archive';
            $config['archive.filename'] = $input['archive_prestashop'];
            $config['archive.version_num'] = $input['archive_num'];
            // $config['archive_name'] = $input['archive_name'];
            $this->logger->info($this->translator->trans('Upgrade process will use archive.', array(), 'Modules.Autoupgrade.Admin'));
        }
        if (isset($input['directory_num'])) {
            $config['channel'] = 'directory';
            if (empty($input['directory_num']) || strpos($input['directory_num'], '.') === false) {
                $this->error = true;
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.', array(), 'Modules.Autoupgrade.Admin'));

                return false;
            }

            $config['directory.version_num'] = $input['directory_num'];
        }
        if (isset($input['skip_backup'])) {
            $config['skip_backup'] = $input['skip_backup'];
        }

        if (!$this->writeConfig($config)) {
            $this->error = true;
            $this->logger->info($this->translator->trans('Error on saving configuration', array(), 'Modules.Autoupgrade.Admin'));
        }
    }

    /**
     * @param array $parameters
     */
    public function inputCLIParameters($parameters)
    {
        $this->isRunInCLI = true;
        $this->cliParameters = $parameters;
    }

    /**
     * Fetch data from either $_REQUEST (web context) or given input (CLI context)
     */
    protected function fetchConfigurationData()
    {
        if ($this->isRunInCLI) {
            return $this->getCLIParameters();
        }
        return $this->getRequestParams();
    }

    protected function getCLIParameters()
    {
        if (empty($this->cliParameters)) {
            throw new \RuntimeException('Empty CLI parameters - did CLI entry point failed to provide data?');
        }

        return $this->cliParameters;
    }


    /**
     * @return array
     */
    protected function getRequestParams()
    {
        return empty($_REQUEST['params']) ? array() : $_REQUEST['params'];
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
        $this->logger->info($this->translator->trans('Configuration successfully updated.', array(), 'Modules.Autoupgrade.Admin') . ' <strong>' . $this->translator->trans('This page will now be reloaded and the module will check if a new version is available.', array(), 'Modules.Autoupgrade.Admin') . '</strong>');

        return (new UpgradeConfigurationStorage($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR))->save($this->container->getUpgradeConfiguration(), UpgradeFileNames::CONFIG_FILENAME);
    }
}
