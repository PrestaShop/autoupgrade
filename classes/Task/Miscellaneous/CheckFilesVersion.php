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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * List the files modified in the current installation regards to the original version.
 */
class CheckFilesVersion extends AbstractTask
{
    /**
     * @throws Exception
     */
    public function run(): int
    {
        // do nothing after this request (see javascript function doAjaxRequest )
        $this->next = '';
        $checksumCompare = $this->container->getChecksumCompare();
        $currentPrestaShopVersion = $this->container->getProperty(UpgradeContainer::PS_VERSION);
        $changedFileList = $checksumCompare->getTamperedFilesOnShop($currentPrestaShopVersion);

        if ($changedFileList === false) {
            $this->nextParams['status'] = 'error';
            $this->nextParams['msg'] = $this->translator->trans('Unable to check files for the installed version of PrestaShop.');

            return ExitCode::FAIL;
        }

        if ($checksumCompare->isAuthenticPrestashopVersion($currentPrestaShopVersion)) {
            $this->nextParams['status'] = 'ok';
            $this->nextParams['msg'] = $this->translator->trans('Core files are ok');
        } else {
            $this->nextParams['status'] = 'warn';
            $this->nextParams['msg'] = $this->translator->trans(
                '%modificationscount% file modifications have been detected, including %coremodifications% from core and native modules:',
                [
                    '%modificationscount%' => count(array_merge($changedFileList['core'], $changedFileList['mail'], $changedFileList['translation'])),
                    '%coremodifications%' => count($changedFileList['core']),
                ]
            );
        }
        $this->nextParams['result'] = $changedFileList;

        $this->container->getFileConfigurationStorage()->save($changedFileList['translation'], UpgradeFileNames::TRANSLATION_FILES_CUSTOM_LIST);
        $this->container->getFileConfigurationStorage()->save($changedFileList['mail'], UpgradeFileNames::MAILS_CUSTOM_LIST);

        return ExitCode::SUCCESS;
    }
}
