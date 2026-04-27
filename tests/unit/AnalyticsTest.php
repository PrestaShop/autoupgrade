<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
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
            UpdateConfiguration::DISABLE_NON_NATIVE_MODULES => false,
            UpdateConfiguration::UNINSTALL_INCOMPATIBLE_MODULES => true,
            UpdateConfiguration::PS_AUTOUP_CHANGE_DEFAULT_THEME => true,
            UpdateConfiguration::REGENERATE_EMAIL_TEMPLATES => true,
            UpdateConfiguration::CHANNEL => UpdateConfiguration::CHANNEL_LOCAL,
            UpdateConfiguration::ARCHIVE_ZIP => 'zip.zip',
            UpdateConfiguration::UPDATE_TYPE => 'patch',
        ]);
        $configurationStorage->save($updateConfiguration);

        $backupConfiguration = $configurationStorage->loadBackupConfiguration();
        $backupConfiguration->merge([
            BackupConfiguration::KEEP_IMAGES => false,
        ]);
        $configurationStorage->save($backupConfiguration);

        $analytics = new Analytics(
            $updateConfiguration,
            $backupConfiguration,
            $this->container->getEnvironment(),
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
            'channel' => 'browser',
            'userId' => 'somePathToAutoupgradeModule',
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
            'channel' => 'browser',
            'userId' => 'somePathToAutoupgradeModule',
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
                    'uninstall_incompatible_modules' => true,
                    'regenerate_customized_email_templates' => true,
                    'regenerate_rtl_stylesheet' => false,
                    'update_type' => 'patch',
                ],
            ],
            $analytics->getProperties(Analytics::WITH_UPDATE_PROPERTIES)
        );

        $this->assertEquals([
            'channel' => 'browser',
            'userId' => 'somePathToAutoupgradeModule',
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
            'channel' => 'browser',
            'userId' => 'somePathToAutoupgradeModule',
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
