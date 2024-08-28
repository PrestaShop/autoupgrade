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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;

class Analytics
{
    const SEGMENT_CLIENT_KEY_PHP = 'NrWZk42rDrA56DkEt9Tj18DBirLoRLhj';
    const SEGMENT_CLIENT_KEY_JS = 'RM87m03McDSL4Fvm3GJ3piBPbAL3Fa2i';

    const WITH_COMMON_PROPERTIES = 0;
    const WITH_UPGRADE_PROPERTIES = 1;
    const WITH_ROLLBACK_PROPERTIES = 2;

    // Reusing environment variable from Distribution API
    public const URL_TRACKING_ENV_NAME = 'PS_URL_TRACKING';

    /**
     * @var string
     */
    private $anonymousId;

    /**
     * @var array<string, mixed>
     */
    private $properties;

    /**
     * @var UpgradeConfiguration
     */
    private $upgradeConfiguration;

    /**
     * @var State
     */
    private $state;

    /**
     * @param string $anonymousUserId
     * @param array{'properties'?: array<string, mixed>} $options
     */
    public function __construct(
        UpgradeConfiguration $upgradeConfiguration,
        State $state,
        $anonymousUserId,
        array $options
    ) {
        $this->upgradeConfiguration = $upgradeConfiguration;
        $this->state = $state;

        $this->anonymousId = hash('sha256', $anonymousUserId, false);
        $this->properties = $options['properties'] ?? [];

        if ($this->hasOptedOut()) {
            return;
        }

        \Segment::init(self::SEGMENT_CLIENT_KEY_PHP);
    }

    /**
     * @param string $event
     * @param self::WITH_*_PROPERTIES $propertiesType
     *
     * @return void
     */
    public function track($event, $propertiesType = self::WITH_COMMON_PROPERTIES)
    {
        if ($this->hasOptedOut()) {
            return;
        }

        \Segment::track(array_merge(
            ['event' => '[SUE] ' . $event],
            $this->getProperties($propertiesType)
        ));
        \Segment::flush();
    }

    /**
     * @param self::WITH_*_PROPERTIES $type
     *
     * @return array<string, mixed>
     */
    public function getProperties($type)
    {
        switch ($type) {
            case self::WITH_UPGRADE_PROPERTIES:
                $additionalProperties = [
                    'from_ps_version' => $this->state->getOriginVersion(),
                    'to_ps_version' => $this->state->getInstallVersion(),
                    'upgrade_channel' => $this->upgradeConfiguration->getChannel(),
                    'backup_files_and_databases' => $this->upgradeConfiguration->shouldBackupFilesAndDatabase(),
                    'backup_images' => $this->upgradeConfiguration->shouldBackupImages(),
                    'server_performance' => $this->upgradeConfiguration->getPerformanceLevel(),
                    'disable_non_native_modules' => $this->upgradeConfiguration->shouldDeactivateCustomModules(),
                    'upgrade_default_theme' => $this->upgradeConfiguration->shouldUpdateDefaultTheme(),
                    'switch_to_default_theme' => $this->upgradeConfiguration->shouldSwitchToDefaultTheme(),
                    'regenerate_rtl_stylesheet' => $this->upgradeConfiguration->shouldUpdateRTLFiles(),
                    'keep_customized_email_templates' => $this->upgradeConfiguration->shouldKeepMails(),
                ];
                break;
            case self::WITH_ROLLBACK_PROPERTIES:
                $additionalProperties = [
                    'from_ps_version' => $this->properties['ps_version'] ?? null,
                    'to_ps_version' => $this->state->getRestoreVersion(),
                ];
                break;
            default:
                $additionalProperties = [];
        }

        return [
            'anonymousId' => $this->anonymousId,
            'channel' => 'browser',
            'properties' => array_merge(
                $this->properties,
                $additionalProperties,
                [
                    'module' => 'autoupgrade',
                ]
            ),
        ];
    }

    private function hasOptedOut(): bool
    {
        return isset($_SERVER[self::URL_TRACKING_ENV_NAME])
            && ((bool) $_SERVER[self::URL_TRACKING_ENV_NAME] === false || $_SERVER[self::URL_TRACKING_ENV_NAME] === 'false');
    }
}
