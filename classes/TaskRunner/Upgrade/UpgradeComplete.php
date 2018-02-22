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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter;

/**
 * Ends the upgrade process and displays the success message
 */
class UpgradeComplete extends AbstractTask
{
    public function run()
    {
        $this->upgradeClass->next_desc = $this->upgradeClass->state->getWarningExists() ?
            $this->upgradeClass->getTranslator()->trans('Upgrade process done, but some warnings have been found.', array(), 'Modules.Autoupgrade.Admin') :
            $this->upgradeClass->getTranslator()->trans('Upgrade process done. Congratulations! You can now reactivate your shop.', array(), 'Modules.Autoupgrade.Admin');

        $this->upgradeClass->next = '';

        if ($this->upgradeClass->upgradeConfiguration->get('channel') != 'archive' && file_exists($this->upgradeClass->getFilePath()) && unlink($this->upgradeClass->getFilePath())) {
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('%s removed', array($this->upgradeClass->getFilePath()), 'Modules.Autoupgrade.Admin');
        } elseif (is_file($this->upgradeClass->getFilePath())) {
            $this->upgradeClass->nextQuickInfo[] = '<strong>'.$this->upgradeClass->getTranslator()->trans('Please remove %s by FTP', array($this->upgradeClass->getFilePath()), 'Modules.Autoupgrade.Admin').'</strong>';
        }

        if ($this->upgradeClass->upgradeConfiguration->get('channel') != 'directory' && file_exists($this->upgradeClass->latestRootDir) && \AdminSelfUpgrade::deleteDirectory($this->upgradeClass->latestRootDir)) {
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('%s removed', array($this->upgradeClass->latestRootDir), 'Modules.Autoupgrade.Admin');
        } elseif (is_dir($this->upgradeClass->latestRootDir)) {
            $this->upgradeClass->nextQuickInfo[] = '<strong>'.$this->upgradeClass->getTranslator()->trans('Please remove %s by FTP', array($this->upgradeClass->latestRootDir), 'Modules.Autoupgrade.Admin').'</strong>';
        }

        (new SymfonyAdapter())->clearMigrationCache();
    }
}