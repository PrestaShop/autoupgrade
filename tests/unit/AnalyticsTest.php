<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Filesystem\Filesystem;

class AnalyticsTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->container = new UpgradeContainer(__DIR__, __DIR__ . '/..');
        $this->filesystemAdapter = $this->container->getFilesystemAdapter();
    }

    public function testProperties()
    {
        $fixturesDir = __DIR__ . '/../../fixtures/config/';
        $fileStorage = new FileStorage(new Filesystem(), $fixturesDir);

        $restoreState = (new RestoreState($fileStorage))
            ->setRestoreName('V1.2.3_blablabla-🐶');
        $updateState = (new UpdateState($fileStorage))
            ->setCurrentVersion('8.8.8')
            ->setDestinationVersion('8.8.808');
        $states = [
            'restore' => $restoreState,
            'update' => $updateState,
        ];
        $configurationStorage = $this->container->getConfigurationStorage();
        $updateConfiguration = $configurationStorage->loadUpdateConfiguration();
        $updateConfiguration->merge([
            UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => false,
            UpgradeConfiguration::PS_AUTOUP_CHANGE_DEFAULT_THEME => true,
            UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL => true,
            UpgradeConfiguration::PS_AUTOUP_KEEP_IMAGES => false,
            UpgradeConfiguration::CHANNEL => UpgradeConfiguration::CHANNEL_LOCAL,
            UpgradeConfiguration::ARCHIVE_ZIP => 'zip.zip',
        ]);
        $configurationStorage->save($updateConfiguration);

        $analytics = new Analytics(
            $updateConfiguration,
            $states,
            'somePathToAutoupgradeModule',
            [
                'properties' => [
                    Analytics::WITH_COMMON_PROPERTIES => [
                        'ps_version' => '8.8.8',
                        'php_version' => '6.0.8',
                        'autoupgrade_version' => '9.8.7',
                    ],
                    Analytics::WITH_UPDATE_PROPERTIES => [
                        'disable_all_overrides' => true,
                        'regenerate_rtl_stylesheet' => false,
                    ],
                ],
            ]
        );

        $this->assertEquals([
            'anonymousId' => 'somePathToAutoupgradeModule',
            'channel' => 'browser',
            'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'module' => 'autoupgrade',
                ],
            ],
            $analytics->getProperties(Analytics::WITH_COMMON_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => 'somePathToAutoupgradeModule',
            'channel' => 'browser',
            'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'disable_all_overrides' => true,
                    'module' => 'autoupgrade',

                    'from_ps_version' => '8.8.8',
                    'to_ps_version' => '8.8.808',
                    'upgrade_channel' => 'local',
                    'disable_non_native_modules' => false,
                    'switch_to_default_theme' => true,
                    'regenerate_customized_email_templates' => true,
                    'regenerate_rtl_stylesheet' => false,
                ],
            ],
            $analytics->getProperties(Analytics::WITH_UPDATE_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => 'somePathToAutoupgradeModule',
            'channel' => 'browser',
            'properties' => [
                'ps_version' => '8.8.8',
                'php_version' => '6.0.8',
                'autoupgrade_version' => '9.8.7',
                'module' => 'autoupgrade',

                'backup_images' => false,
            ],
        ],
            $analytics->getProperties(Analytics::WITH_BACKUP_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => 'somePathToAutoupgradeModule',
            'channel' => 'browser',
            'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'module' => 'autoupgrade',

                    'from_ps_version' => '8.8.8',
                    'to_ps_version' => '1.2.3',
                ],
            ],
            $analytics->getProperties(Analytics::WITH_RESTORE_PROPERTIES)
        );
    }
}
