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

use Configuration;
use PrestaShop\Module\AutoUpgrade\ChannelInfo;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class UpgradeButtonBlock
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Upgrader
     */
    private $upgrader;

    /**
     * @var UpgradeConfiguration
     */
    private $config;

    /**
     * @var UpgradeSelfCheck
     */
    private $selfCheck;

    /**
     * @var string
     */
    private $downloadPath;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    private $manualMode;

    /**
     * UpgradeButtonBlock constructor.
     *
     * @param Environment $twig
     * @param Translator $translator
     * @param UpgradeConfiguration $config
     * @param Upgrader $upgrader
     * @param UpgradeSelfCheck $selfCheck
     */
    public function __construct(
        $twig,
        TranslatorInterface $translator,
        UpgradeConfiguration $config,
        Upgrader $upgrader,
        UpgradeSelfCheck $selfCheck,
        $downloadPath,
        $token,
        $manualMode
    ) {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->upgrader = $upgrader;
        $this->config = $config;
        $this->selfCheck = $selfCheck;
        $this->downloadPath = $downloadPath;
        $this->token = $token;
        $this->manualMode = $manualMode;
    }

    /**
     * display the summary current version / target version + "Upgrade Now" button with a "more options" button.
     *
     * @return string HTML
     */
    public function render()
    {
        $translator = $this->translator;

        $versionCompare = $this->upgrader->version_num !== null
            ? version_compare(_PS_VERSION_, $this->upgrader->version_num)
            : 0
        ;
        $channel = $this->config->get('channel');

        if (!in_array($channel, ['archive', 'directory']) && !empty($this->upgrader->version_num)) {
            $latestVersion = "{$this->upgrader->version_name} - ({$this->upgrader->version_num})";
        } else {
            $latestVersion = $translator->trans('N/A', [], 'Modules.Autoupgrade.Admin');
        }

        $showUpgradeButton = false;
        $showUpgradeLink = false;
        $upgradeLink = '';
        $changelogLink = '';
        $skipActions = [];

        // decide to display "Start Upgrade" or not
        if ($this->selfCheck->isOkForUpgrade() && $versionCompare < 0) {
            $showUpgradeButton = true;
            if (!in_array($channel, ['archive', 'directory'])) {
                if ($channel == 'private') {
                    $this->upgrader->link = $this->config->get('private_release_link');
                }

                $showUpgradeLink = true;
                $upgradeLink = $this->upgrader->link;
                $changelogLink = $this->upgrader->changelog;
            }

            // if skipActions property is used, we will handle that in the display :)
            $skipActions = AbstractTask::$skipAction;
        }

        if (empty($channel)) {
            $channel = Upgrader::DEFAULT_CHANNEL;
        }

        $dir = glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.zip');
        $xml = glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.xml');

        $data = [
            'versionCompare' => $versionCompare,
            'currentPsVersion' => _PS_VERSION_,
            'latestChannelVersion' => $latestVersion,
            'channel' => $channel,
            'showUpgradeButton' => $showUpgradeButton,
            'upgradeLink' => $upgradeLink,
            'showUpgradeLink' => $showUpgradeLink,
            'changelogLink' => $changelogLink,
            'skipActions' => $skipActions,
            'lastVersionCheck' => Configuration::get('PS_LAST_VERSION_CHECK'),
            'token' => $this->token,
            'channelOptions' => $this->getOptChannels(),
            'channelInfoBlock' => $this->buildChannelInfoBlock($channel),
            'privateChannel' => [
                'releaseLink' => $this->config->get('private_release_link'),
                'releaseMd5' => $this->config->get('private_release_md5'),
                'allowMajor' => $this->config->get('private_allow_major'),
            ],
            'archiveFiles' => $dir,
            'xmlFiles' => $xml,
            'archiveFileName' => $this->config->get('archive.filename'),
            'xmlFileName' => $this->config->get('archive.xml'),
            'archiveVersionNumber' => $this->config->get('archive.version_num'),
            'downloadPath' => $this->downloadPath . DIRECTORY_SEPARATOR,
            'directoryVersionNumber' => $this->config->get('directory.version_num'),
            'manualMode' => $this->manualMode,
            'phpVersion' => PHP_VERSION,
        ];

        return $this->twig->render('@ModuleAutoUpgrade/block/upgradeButtonBlock.twig', $data);
    }

    /**
     * @return array
     */
    private function getOptChannels()
    {
        $translator = $this->translator;

        return [
            // Hey ! I'm really using a fieldset element to regroup fields ?! !
            ['useMajor', 'major', $translator->trans('Major release', [], 'Modules.Autoupgrade.Admin')],
            ['useMinor', 'minor', $translator->trans('Minor release (recommended)', [], 'Modules.Autoupgrade.Admin')],
            ['useRC', 'rc', $translator->trans('Release candidates', [], 'Modules.Autoupgrade.Admin')],
            ['useBeta', 'beta', $translator->trans('Beta releases', [], 'Modules.Autoupgrade.Admin')],
            ['useAlpha', 'alpha', $translator->trans('Alpha releases', [], 'Modules.Autoupgrade.Admin')],
            ['usePrivate', 'private', $translator->trans('Private release (require link and MD5 hash)', [], 'Modules.Autoupgrade.Admin')],
            ['useArchive', 'archive', $translator->trans('Local archive', [], 'Modules.Autoupgrade.Admin')],
            ['useDirectory', 'directory', $translator->trans('Local directory', [], 'Modules.Autoupgrade.Admin')],
        ];
    }

    private function getInfoForChannel($channel)
    {
        return new ChannelInfo($this->upgrader, $this->config, $channel);
    }

    /**
     * @param string $channel
     *
     * @return string
     */
    private function buildChannelInfoBlock($channel)
    {
        $channelInfo = $this->getInfoForChannel($channel);

        return (new ChannelInfoBlock($this->config, $channelInfo, $this->twig))
            ->render();
    }
}
