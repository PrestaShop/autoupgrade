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
            'header' => [
                'switch' => [
                    'left' => $this->trans('Basic mode', 'Header'),
                    'right' => $this->trans('Expert mode', 'Header'),
                ],
            ],
            'modal' => [
                'close' => $this->trans('Close', 'Modal'),
                'cancel' => $this->trans('Cancel', 'Modal'),
                'confirm' => $this->trans('Confirm', 'Modal'),
                'title' => $this->trans('Modal title', 'Modal'),
            ],
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
                'buttons' => [
                    'continue' => $this->trans('Continue to pre-upgrade checklist', 'Version'),
                ],
            ],
            'preUpgrade' => [
                'title' => $this->trans('Pre-upgrade checklist', 'Preupgrade'),
                'description' => $this->trans('Before starting the upgrade process, please make sure this checklist is all green', 'Preupgrade'),
                'list' => [
                    'backup' => $this->trans('Make a full backup of your store', 'Preupgrade'),
                    'maintenance' => $this->trans('Your store is in maintenance mode', 'Preupgrade'),
                    'max_execution_time' => $this->trans('PHP\'s "max_execution_time" settings has a high value or is disabled entirely (current value: %d)', 'Preupgrade'),
                    'is_writable' => $this->trans('Your store\'s root directory is writable (with appropriate permissions)', 'Preupgrade'),
                    'allow_url_fopen' => $this->trans('PHP\'s "allow_url_fopen" option is turned on, or cURL is installed', 'Preupgrade'),
                ],
                'modules' => [
                    'title' => $this->trans('Module compatibility:', 'Preupgrade'),
                    'description' => $this->trans('As non-native modules can experience some compatibility issues, we recommend to disabled them by default. Keepin them enable might prevent you from loading the "Modules" page properly after th upgrade. Check also the %slist of native modules in 1.7%s to make sure lorem loris.', 'Preupgrade'),
                    'help' => $this->trans('Modules will be placed in a folder for lorem ipsum atmerit', 'Preupgrade'),
                    'list' => [
                        'compatibility' => $this->trans('I understand that not all my modules might be compatible with the version I\'m going to upgrade to', 'Preupgrade'),
                        'native_modules' => $this->trans('I understand that some of my native modules might loose previous data after upgrade.', 'Preupgrade'),
                        'experience' => $this->trans('I understand  that I might experience some lorem problems blanditis vuluptatum', 'Preupgrade'),
                    ]
                ],
                'core' => [
                    'title' => $this->trans('Modified core files:', 'Preupgrade'),
                    'description' => $this->trans('The following core files have been modified, modifications will be lost during the upgrade.', 'Preupgrade'),
                    'list' => [
                        'understand' => $this->trans('I understand that blanditis praesentium voluptatum files', 'Preupgrade'),
                    ]
                ],
                'modal' => [
                    'start' => [
                        'title' => $this->trans('Start your upgrade now?', 'Preupgrade'),
                        'description' => $this->trans('Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium dolorempque', 'Preupgrade'),
                    ]
                ],
                'buttons' => [
                    'backup' => $this->trans('One click back-up', 'Preupgrade'),
                    'disableModules' => $this->trans('Disable all modules', 'Preupgrade'),
                    'upgrade' => $this->trans('Upgrade PrestaShop', 'Preupgrade'),
                    'maintenance' => $this->trans('Switch to maintenance mode', 'Preupgrade'),
                ],
            ],
            'upgrade' => [
                'title' => $this->trans('Upgrade processing', 'Upgrade'),
                'description' => $this->trans('Depending on your lorem this can take up to lorem minutes', 'Upgrade'),
            ],
        ];

        return $translations;
    }

    protected function trans($message, $moduleDomain, $params = [])
    {
        return $this->controller->trans($message, $params, 'Modules.Autoupgrade.' . $moduleDomain);
    }
}
