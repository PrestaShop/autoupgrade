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

namespace PrestaShop\Module\AutoUpgrade\Twig\Block;

use PrestaShop\Module\AutoUpgrade\ChannelInfo;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use Twig_Environment;

class ChannelInfoBlock
{
    /**
     * @var UpgradeConfiguration
     */
    private $config;

    /**
     * @var ChannelInfo
     */
    private $channelInfo;

    /**
     * @var Twig_Environment|\Twig\Environment
     */
    private $twig;

    /**
     * ChannelInfoBlock constructor.
     *
     * @param UpgradeConfiguration $config
     * @param ChannelInfo $channelInfo
     * @param Twig_Environment|\Twig\Environment $twig
     */
    public function __construct(UpgradeConfiguration $config, ChannelInfo $channelInfo, $twig)
    {
        $this->config = $config;
        $this->channelInfo = $channelInfo;
        $this->twig = $twig;
    }

    /**
     * @return string HTML
     */
    public function render()
    {
        $channel = $this->channelInfo->getChannel();
        $upgradeInfo = $this->channelInfo->getInfo();

        if ($channel == 'private') {
            $upgradeInfo['link'] = $this->config->get('private_release_link');
            $upgradeInfo['md5'] = $this->config->get('private_release_md5');
        }

        return $this->twig->render(
            '@ModuleAutoUpgrade/block/channelInfo.twig',
            [
                'upgradeInfo' => $upgradeInfo,
                'allPhpVersions' => UpgradeSelfCheck::PHP_VERSIONS_DISPLAY,
                'psPhpCompatibilityRanges' => $this->buildCompatibilityTableDisplay(),
            ]
        );
    }

    /**
     * Builds array of formatted data for the compatibility table display
     *
     * @return array
     */
    public function buildCompatibilityTableDisplay()
    {
        $startPrestaShopVersion = $previousPHPRange = null;
        $i = 0;
        foreach (UpgradeSelfCheck::PHP_PS_VERSIONS as $prestashopVersion => $phpVersions) {
            $i++;
            if (is_null($startPrestaShopVersion)) {
                $startPrestaShopVersion = $prestashopVersion;
                $previousPHPRange = $phpVersions;
            }
            $isCurrentPrestaVersion = $this->isCurrentPrestashopVersion($startPrestaShopVersion);
            if ($phpVersions === $previousPHPRange) {
                $previousPrestaVersion = $prestashopVersion;
            } else {
                $label = $this->buildPSLabel($startPrestaShopVersion, $previousPrestaVersion);
                $result[$label]['php_versions'] = $this->buildPhpVersionsList($previousPHPRange);
                $result[$label]['is_current'] = $isCurrentPrestaVersion;
                $startPrestaShopVersion = $prestashopVersion;
                $previousPrestaVersion = null;
            }
            if ($i === count(UpgradeSelfCheck::PHP_PS_VERSIONS)) {
                $result[$prestashopVersion]['php_versions'] = $this->buildPhpVersionsList($phpVersions);
                $result[$prestashopVersion]['is_current'] = $isCurrentPrestaVersion;
            }
            $previousPHPRange = $phpVersions;
        }

        return $result;
    }

    /**
     * Builds PrestaShop version label for display
     *
     * @param strin $startVersion
     * @param string $endVersion
     * @return string
     */
    public function buildPSLabel(strin $startVersion, string $endVersion): string
    {
        if ($startVersion === '1.6.1.18') {
            return '>= 1.6.1.18';
        }

        return $startVersion .= $endVersion ? ' ~ ' . $endVersion : '';
    }

    /**
     * Builds a list of php versions for a given php version range
     *
     * @param array $phpVersionRange
     * @return array
     */
    public function buildPhpVersionsList($phpVersionRange) {
        $phpStart = $phpVersionRange[0];
        $phpEnd = $phpVersionRange[1];
        $phpVersionsList = [];
        $inRange = false;
        foreach (UpgradeSelfCheck::PHP_VERSIONS_DISPLAY as $phpVersion) {
            if ($phpVersion === $phpStart) {
                $inRange = true;
            }
            if ($inRange) {
                $phpVersionsList[] = $phpVersion;
            }
            if ($phpVersion === $phpEnd) {
                break;
            }
        }

        return $phpVersionsList;
    }

    /**
     * Find out if a given prestashop version is equal to the one currently used
     * (not taking patch versions into account)
     *
     * @param array $prestaversion
     * @return bool
     */
    public function isCurrentPrestashopVersion($prestaversion)
    {
        // special case for 1.6.1 versions
        if (substr(_PS_VERSION_, 0, 5) === '1.6.1' && $prestaversion === '1.6.1.18') {
            return version_compare(_PS_VERSION_, $prestaversion, '>=');
        }
        $explodedCurrentPSVersion = explode('.', _PS_VERSION_);
        $shortenCurrentPrestashop = implode('.', array_slice($explodedCurrentPSVersion, 0, 3));

        return $prestaversion === $shortenCurrentPrestashop;
    }
}
