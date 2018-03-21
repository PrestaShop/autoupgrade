<?php
/*
 * 2007-2018 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
 * update configuration after validating the new values
 */
class UpdateConfig extends AbstractTask
{
    public function run()
    {
        $config = array();
        // nothing next
        $this->upgradeClass->next = '';
        // update channel
        if (isset($this->upgradeClass->currentParams['channel'])) {
            $config['channel'] = $this->upgradeClass->currentParams['channel'];
        }
        if (isset($this->upgradeClass->currentParams['private_release_link']) && isset($this->upgradeClass->currentParams['private_release_md5'])) {
            $config['channel'] = 'private';
            $config['private_release_link'] = $this->upgradeClass->currentParams['private_release_link'];
            $config['private_release_md5'] = $this->upgradeClass->currentParams['private_release_md5'];
            $config['private_allow_major'] = $this->upgradeClass->currentParams['private_allow_major'];
        }
        // if (!empty($this->upgradeClass->currentParams['archive_name']) && !empty($this->upgradeClass->currentParams['archive_num']))
        if (!empty($this->upgradeClass->currentParams['archive_prestashop'])) {
            $file = $this->upgradeClass->currentParams['archive_prestashop'];
            if (!file_exists($this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH).DIRECTORY_SEPARATOR.$file)) {
                $this->upgradeClass->error = 1;
                $this->logger->info($this->translator->trans('File %s does not exist. Unable to select that channel.', array($file), 'Modules.Autoupgrade.Admin'));
                return false;
            }
            if (empty($this->upgradeClass->currentParams['archive_num'])) {
                $this->upgradeClass->error = 1;
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.', array(), 'Modules.Autoupgrade.Admin'));
                return false;
            }
            $config['channel'] = 'archive';
            $config['archive.filename'] = $this->upgradeClass->currentParams['archive_prestashop'];
            $config['archive.version_num'] = $this->upgradeClass->currentParams['archive_num'];
            // $config['archive_name'] = $this->upgradeClass->currentParams['archive_name'];
            $this->logger->info($this->translator->trans('Upgrade process will use archive.', array(), 'Modules.Autoupgrade.Admin'));
        }
        if (isset($this->upgradeClass->currentParams['directory_num'])) {
            $config['channel'] = 'directory';
            if (empty($this->upgradeClass->currentParams['directory_num']) || strpos($this->upgradeClass->currentParams['directory_num'], '.') === false) {
                $this->upgradeClass->error = 1;
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.', array(), 'Modules.Autoupgrade.Admin'));
                return false;
            }

            $config['directory.version_num'] = $this->upgradeClass->currentParams['directory_num'];
        }
        if (isset($this->upgradeClass->currentParams['skip_backup'])) {
            $config['skip_backup'] = $this->upgradeClass->currentParams['skip_backup'];
        }

        if (!$this->upgradeClass->writeConfig($config)) {
            $this->upgradeClass->error = 1;
            $this->logger->info($this->translator->trans('Error on saving configuration', array(), 'Modules.Autoupgrade.Admin'));
        }
    }
}