<?php
/**
 * 2007-2019 PrestaShop.
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade;

use AdminController;

class Translations
{
    /**
     * @var AdminController
     */
    private $controller = null;

    public function __construct(AdminController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Create all tranlations (backoffice)
     *
     * @return array translation list
     */
    public function getTranslations($locale)
    {
        $translations[$locale] = array(
            'main' => array(
                'welcome' => $this->controller->trans('Welcome!', [], 'Modules.Autoupgrade.Welcome'),
                'intro' => $this->controller->trans('With the PrestaShop 1-Click Upgrade module, upgrading your store to the latest version available has never been easier!', [], 'Modules.Autoupgrade.Welcome'),
                'rollback' => $this->controller->trans('Double-check the integrity of your backup and that you can easily manually roll-back if necessary.', [], 'Modules.Autoupgrade.Welcome'),
                'hostProvider' => $this->controller->trans('If you do not know how to proceed, ask your hosting provider.', [], 'Modules.Autoupgrade.Welcome'),
                'backup' => $this->controller->trans('Please always perform a full manual backup of your files and database before starting any upgrade.', [], 'Modules.Autoupgrade.Welcome'),
                'choice' => [
                    'basic' => $this->controller->trans('Basic mode (recommended)', [], 'Modules.Autoupgrade.Welcome'),
                    'expert' => $this->controller->trans('Expert mode', [], 'Modules.Autoupgrade.Welcome'),
                ]
            ),
        );

        return $translations;
    }
}
