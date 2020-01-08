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
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
 * List the files modified in the current installation regards to the original version.
 */
class CheckFilesVersion extends AbstractTask
{
    public function run()
    {
        // do nothing after this request (see javascript function doAjaxRequest )
        $this->next = '';
        $upgrader = $this->container->getUpgrader();
        $changedFileList = $upgrader->getChangedFilesList();

        if ($changedFileList === false) {
            $this->nextParams['status'] = 'error';
            $this->nextParams['msg'] = $this->translator->trans('Unable to check files for the installed version of PrestaShop.', array(), 'Modules.Autoupgrade.Admin');

            return;
        }

        foreach (array('core', 'translation', 'mail') as $type) {
            if (!isset($changedFileList[$type])) {
                $changedFileList[$type] = array();
            }
        }

        if ($upgrader->isAuthenticPrestashopVersion() === true) {
            $this->nextParams['status'] = 'ok';
            $this->nextParams['msg'] = $this->translator->trans('Core files are ok', array(), 'Modules.Autoupgrade.Admin');
        } else {
            $this->nextParams['status'] = 'warn';
            $this->nextParams['msg'] = $this->translator->trans(
                '%modificationscount% file modifications have been detected, including %coremodifications% from core and native modules:',
                array(
                    '%modificationscount%' => count(array_merge($changedFileList['core'], $changedFileList['mail'], $changedFileList['translation'])),
                    '%coremodifications%' => count($changedFileList['core']),
                ),
                'Modules.Autoupgrade.Admin'
            );
        }
        $this->nextParams['result'] = $changedFileList;

        $this->container->getFileConfigurationStorage()->save($changedFileList['translation'], UpgradeFileNames::TRANSLATION_FILES_CUSTOM_LIST);
        $this->container->getFileConfigurationStorage()->save($changedFileList['mail'], UpgradeFileNames::MAILS_CUSTOM_LIST);
    }
}
