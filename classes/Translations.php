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
        $translations[$locale] = [
            'on' => $this->trans('On', 'Default'),
            'off' => $this->trans('Off', 'Default'),
            'main' => [
                'welcome' => $this->trans('Welcome!', 'Welcome'),
                'intro' => $this->trans('With the PrestaShop 1-Click Upgrade module, upgrading your store to the latest version available has never been easier!', 'Welcome'),
                'rollback' => $this->trans('Double-check the integrity of your backup and that you can easily manually roll-back if necessary.', 'Welcome'),
                'hostProvider' => $this->trans('If you do not know how to proceed, ask your hosting provider.', 'Welcome'),
                'backup' => $this->trans('Please always perform a full manual backup of your files and database before starting any upgrade.', 'Welcome'),
                'choice' => [
                    'basic' => $this->trans('Basic mode (recommended)', 'Welcome'),
                    'expert' => $this->trans('Expert mode', 'Welcome'),
                ]
            ],
            'steps' => [
                'choice' => $this->trans('Version choice', 'Step'),
                'prepare' => $this->trans('Pre-upgrade', 'Step'),
                'upgrade' => $this->trans('Upgrade', 'Step'),
                'postUpgrade' => $this->trans('Post-upgrade', 'Step'),
            ],
            'version' => [
                'title' => $this->trans('Version choice:', 'Version'),
                'description' => $this->trans('Select the version of Prestashop you would like to upgrade', 'Version'),
                'currentVersion' => $this->trans('Your current PrestaShop version:', 'Version'),
                'upgradeVersion' => $this->trans('Choose your upgrade version:', 'Version'),
                'whatsNew' => $this->trans('What\'s new?', 'Version'),
                'options' => [
                    'title' => $this->trans('Upgrade options:', 'Version'),
                    'form' => [
                        'upgradeDefaultTheme' => [
                            'label' => $this->trans('Upgrade the default theme', 'Version'),
                            'description' => $this->trans('If you customize the defautl PrestaShop theme in its folder (folder name "classic" in 1.7), enabling this option will lose your modifications. If you are using your own theme, enabling this option will simply update the default theme files, and your own theme will be safe.', 'Version'),
                        ],
                        'switchToDefaultTheme' => [
                            'label' => $this->trans('Switch to the default theme', 'Version'),
                            'description' => $this->trans('This will change your theme: your shop will then use the default theme of the version of PrestaShop you are upgrading to.', 'Version'),
                        ],
                        'keepCustomizedTemplates' => [
                            'label' => $this->trans('Keep the customized email templates', 'Version'),
                            'description' => $this->trans('This will not upgrade the default PrestaShop e-mails. If you customize the default PrestaShop e-mail templates, enabling this option will keep your modifications.', 'Version'),
                        ],
                    ]
                ],

            ],
        ];

        return $translations;
    }

    protected function trans($message, $domain, $params = [])
    {
        return $this->controller->trans($message, $params, 'Modules.Autoupgrade.' . $domain);
    }
}
