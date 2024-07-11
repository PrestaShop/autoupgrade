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

namespace PrestaShop\Module\AutoUpgrade\Task\Miscellaneous;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\VersionUtils;

/**
 * This class gets the list of all modified and deleted files between current version
 * and target version (according to channel configuration).
 */
class CompareReleases extends AbstractTask
{
    /**
     * @throws Exception
     */
    public function run(): int
    {
        // do nothing after this request (see javascript function doAjaxRequest )
        $this->next = '';
        $channel = $this->container->getUpgradeConfiguration()->get('channel');
        $upgrader = $this->container->getUpgrader();
        $checksumCompare = $this->container->getChecksumCompare();
        switch ($channel) {
            case 'archive':
                $version = $this->container->getUpgradeConfiguration()->get('archive.version_num');
                break;
            case 'directory':
                $version = $this->container->getUpgradeConfiguration()->get('directory.version_num');
                break;
            default:
                $upgrader->branch = VersionUtils::splitPrestaShopVersion(_PS_VERSION_)['major'];
                $upgrader->channel = $channel;
                if ($this->container->getUpgradeConfiguration()->get('channel') == 'private' && !$this->container->getUpgradeConfiguration()->get('private_allow_major')) {
                    $upgrader->checkPSVersion(false, ['private', 'minor']);
                } else {
                    $upgrader->checkPSVersion(false, ['minor']);
                }
                $version = $upgrader->version_num;
        }

        // Get list of differences between these two versions. The differences will be fetched from a local
        // XML file if it exists, or from PrestaShop API.
        $diffFileList = $checksumCompare->getFilesDiffBetweenVersions(_PS_VERSION_, $version);
        if (!is_array($diffFileList)) {
            $this->nextParams['status'] = 'error';
            $this->nextParams['msg'] = sprintf('Unable to generate diff file list between %1$s and %2$s.', _PS_VERSION_, $version);
        } else {
            $this->container->getFileConfigurationStorage()->save($diffFileList, UpgradeFileNames::FILES_DIFF_LIST);
            $this->nextParams['msg'] = $this->translator->trans(
                '%modifiedfiles% files will be modified, %deletedfiles% files will be deleted (if they are found).',
                [
                    '%modifiedfiles%' => count($diffFileList['modified']),
                    '%deletedfiles%' => count($diffFileList['deleted']),
                ]);
            $this->nextParams['result'] = $diffFileList;
        }

        return ExitCode::SUCCESS;
    }
}
