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
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\State;

class AnalyticsTest extends TestCase
{
    public function testProperties()
    {
        $state = (new State())
            ->setOriginVersion('8.8.8')
            ->setInstallVersion('8.8.808')
            ->setRestoreName('V1.2.3_blablabla-🐶');
        $upgradeConfiguration = (new UpgradeConfiguration([
            'PS_AUTOUP_PERFORMANCE' => 5,
            'PS_AUTOUP_CUSTOM_MOD_DESACT' => 0,
            'PS_AUTOUP_UPDATE_DEFAULT_THEME' => 1,
            'PS_AUTOUP_CHANGE_DEFAULT_THEME' => 1,
            'PS_AUTOUP_KEEP_MAILS' => 0,
            'PS_AUTOUP_BACKUP' => 1,
            'PS_AUTOUP_KEEP_IMAGES' => 0,
            'channel' => 'major',
            'archive.filename' => 'zip.zip',
        ]));

        $analytics = new Analytics(
            $upgradeConfiguration,
            $state,
            'somePathToAutoupgradeModule',
            [
                'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'disable_all_overrides' => true,
                ],
            ]
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => array_merge(
                [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'disable_all_overrides' => true,
                    'module' => 'autoupgrade',
                ]),
            ],
            $analytics->getProperties(Analytics::WITH_COMMON_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => array_merge(
                [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'disable_all_overrides' => true,
                    'module' => 'autoupgrade',

                    'from_ps_version' => '8.8.8',
                    'to_ps_version' => '8.8.808',
                    'upgrade_channel' => 'major',
                    'backup_files_and_databases' => true,
                    'backup_images' => false,
                    'server_performance' => 4,
                    'disable_non_native_modules' => false,
                    'upgrade_default_theme' => true,
                    'switch_to_default_theme' => true,
                    'regenerate_rtl_stylesheet' => false,
                    'keep_customized_email_templates' => false,
                ]),
            ],
            $analytics->getProperties(Analytics::WITH_UPGRADE_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => array_merge(
                [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'disable_all_overrides' => true,
                    'module' => 'autoupgrade',

                    'from_ps_version' => '8.8.8',
                    'to_ps_version' => '1.2.3',
                ]),
            ],
            $analytics->getProperties(Analytics::WITH_ROLLBACK_PROPERTIES)
        );
    }
}
