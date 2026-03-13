<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;

class Analytics
{
    const SEGMENT_CLIENT_KEY_PHP = 'NrWZk42rDrA56DkEt9Tj18DBirLoRLhj';

    const WITH_COMMON_PROPERTIES = 0;
    const WITH_UPDATE_PROPERTIES = 1;
    const WITH_BACKUP_PROPERTIES = 2;
    const WITH_RESTORE_PROPERTIES = 3;

    // Reusing environment variable from Distribution API
    public const URL_TRACKING_ENV_NAME = 'PS_URL_TRACKING';

    /**
     * @var string
     */
    private $anonymousId;

    /**
     * @var array<int, array<string, mixed>>
     */
    private $properties;

    /**
     * @var UpdateConfiguration
     */
    private $updateConfiguration;

    /**
     * @var BackupConfiguration
     */
    private $backupConfiguration;

    /**
     * @var array{'restore': RestoreState, 'update': UpdateState}
     */
    private $states;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @param array{'properties'?: array<int, array<string, mixed>>} $options
     * @param array{'restore': RestoreState, 'update': UpdateState} $states
     */
    public function __construct(
        UpdateConfiguration $updateConfiguration,
        BackupConfiguration $backupConfiguration,
        Environment $environment,
        array $states,
        string $anonymousUserId,
        array $options
    ) {
        $this->updateConfiguration = $updateConfiguration;
        $this->backupConfiguration = $backupConfiguration;
        $this->states = $states;

        $this->anonymousId = $anonymousUserId;
        $this->properties = $options['properties'] ?? [];
        $this->environment = $environment;

        if (!$this->environment->getBoolean(Environment::URL_TRACKING_ENV_NAME, true)) {
            return;
        }

        \Segment::init(self::SEGMENT_CLIENT_KEY_PHP);
    }

    /**
     * @param string $event
     * @param self::WITH_*_PROPERTIES $propertiesType
     * @param array<string, mixed> $extraProperties
     */
    public function track(string $event, $propertiesType = self::WITH_COMMON_PROPERTIES, array $extraProperties = []): void
    {
        if (!$this->environment->getBoolean(Environment::URL_TRACKING_ENV_NAME, true)) {
            return;
        }

        $dataToSend = array_merge(
            ['event' => '[SUE] ' . $event],
            $this->getProperties($propertiesType)
        );

        $dataToSend['properties'] = array_merge(
            $dataToSend['properties'],
            $extraProperties
        );

        \Segment::track($dataToSend);
        \Segment::flush();
    }

    /**
     * @param self::WITH_*_PROPERTIES $type
     *
     * @return array<string, mixed>
     */
    public function getProperties($type): array
    {
        switch ($type) {
            case self::WITH_BACKUP_PROPERTIES:
                $additionalProperties = [
                    'backup_images' => $this->backupConfiguration->shouldBackupImages(),
                ];
                $upgradeProperties = $this->properties[self::WITH_BACKUP_PROPERTIES] ?? [];
                $additionalProperties = array_merge($upgradeProperties, $additionalProperties);
                break;
            case self::WITH_UPDATE_PROPERTIES:
                $additionalProperties = [
                    'from_ps_version' => $this->states['update']->getCurrentVersion(),
                    'to_ps_version' => $this->states['update']->getDestinationVersion(),
                    'upgrade_channel' => $this->updateConfiguration->getChannel(),
                    'update_type' => $this->updateConfiguration->getUpdateType(),
                    'disable_non_native_modules' => $this->updateConfiguration->shouldDeactivateCustomModules(),
                    'uninstall_incompatible_modules' => $this->updateConfiguration->shouldUninstallNonCompatibleModules(),
                    'regenerate_customized_email_templates' => $this->updateConfiguration->shouldRegenerateMailTemplates(),
                ];
                $upgradeProperties = $this->properties[self::WITH_UPDATE_PROPERTIES] ?? [];
                $additionalProperties = array_merge($upgradeProperties, $additionalProperties);
                break;
            case self::WITH_RESTORE_PROPERTIES:
                $additionalProperties = [
                    'from_ps_version' => $this->properties[self::WITH_COMMON_PROPERTIES]['ps_version'] ?? null,
                    'to_ps_version' => $this->states['restore']->getRestoreVersion(),
                ];
                $rollbackProperties = $this->properties[self::WITH_RESTORE_PROPERTIES] ?? [];
                $additionalProperties = array_merge($rollbackProperties, $additionalProperties);
                break;
            default:
                $additionalProperties = [];
        }

        $commonProperties = $this->properties[self::WITH_COMMON_PROPERTIES] ?? [];

        return [
            'userId' => $this->anonymousId,
            'channel' => 'browser',
            'properties' => array_merge(
                $commonProperties,
                $additionalProperties,
                [
                    'module' => 'autoupgrade',
                ]
            ),
        ];
    }
}
