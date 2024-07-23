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

class ChannelInfo
{
    /**
     * @var array<string, mixed>
     */
    private $info = [];

    /**
     * @var string
     */
    private $channel;

    public function __construct(Upgrader $upgrader, UpgradeConfiguration $config, string $channel)
    {
        $this->channel = $channel;
        $publicChannels = ['minor', 'major', 'rc', 'beta', 'alpha'];

        $upgrader->branch = VersionUtils::splitPrestaShopVersion(_PS_VERSION_)['major'];
        $upgrader->channel = $channel;

        if (in_array($channel, $publicChannels)) {
            if ($channel == 'private' && !$config->get('private_allow_major')) {
                $upgrader->checkPSVersion(false, ['private', 'minor']);
            } else {
                $upgrader->checkPSVersion(false, ['minor']);
            }

            $this->info = [
                'branch' => $upgrader->branch,
                'available' => $upgrader->available,
                'version_num' => $upgrader->version_num,
                'version_name' => $upgrader->version_name,
                'link' => $upgrader->link,
                'md5' => $upgrader->md5,
                'changelog' => $upgrader->changelog,
            ];

            return;
        }

        switch ($channel) {
            case 'private':
                if (!$config->get('private_allow_major')) {
                    $upgrader->checkPSVersion(false, ['private', 'minor']);
                } else {
                    $upgrader->checkPSVersion(false, ['minor']);
                }

                $this->info = [
                    'available' => $upgrader->available,
                    'branch' => $upgrader->branch,
                    'version_num' => $upgrader->version_num,
                    'version_name' => $upgrader->version_name,
                    'link' => $config->get('private_release_link'),
                    'md5' => $config->get('private_release_md5'),
                    'changelog' => $upgrader->changelog,
                ];
                break;

            case 'archive':
            case 'directory':
                $this->info = [
                    'available' => true,
                ];
                break;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }
}
