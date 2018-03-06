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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner;

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\TaskRepository;

/**
 * Execute the whole upgrade process in a single request.
 * TODO: Handle customization
 */
class AllUpgradeTasks extends AbstractTask
{
    const initialTask = 'upgradeNow';

    public function run()
    {
        $step = self::initialTask;

        $this->init();
        while ($this->canContinue($step)) {
            echo "\n=== Step ".$step."\n";
            $controller = TaskRepository::get($step, $this->upgradeClass);
            $controller->run();

            $step = $this->upgradeClass->next;
        }

        return (int) ($this->upgradeClass->error || count($this->upgradeClass->nextErrors));
    }

    public function setOptions($options)
    {
        if (!empty($options['channel'])) {
            $this->upgradeClass->writeConfig(array(
                'channel' => $options['channel'],
            ));
            $this->upgradeClass->getUpgrader()->channel = $options['channel'];
            $this->upgradeClass->getUpgrader()->checkPSVersion();
        }
    }


    /**
     * Tell the while loop if it can continue
     *
     * @param string $step current step
     * @return boolean
     */
    protected function canContinue($step)
    {
        if (empty($step)) {
            return false;
        }

        if ($this->upgradeClass->error) {
            return false;
        }

        return ! in_array($step, array('error'));
    }

    /**
     * Set default config for AdminSelfUpgrade
     */
    protected function init()
    {
        $this->upgradeClass->writeConfig(array(
            'channel' => 'major',
            'PS_AUTOUP_PERFORMANCE' => 1,
            'PS_AUTOUP_CUSTOM_MOD_DESACT' => 1,
            'PS_AUTOUP_UPDATE_DEFAULT_THEME' => 1,
            'PS_AUTOUP_CHANGE_DEFAULT_THEME' => 0,
            'PS_AUTOUP_KEEP_MAILS' => 0,
            'PS_AUTOUP_BACKUP' => 1,
            'PS_AUTOUP_KEEP_IMAGES' => 0,
        ));

        $_COOKIE['id_employee'] = 1;
        $this->upgradeClass->initDefaultState();
    }
}