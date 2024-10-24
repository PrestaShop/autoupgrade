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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Twig\Environment;

class UpgradeButtonBlock
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Translator
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
     * @param Environment $twig
     */
    public function __construct(
        $twig,
        Translator $translator,
        UpgradeConfiguration $config,
        Upgrader $upgrader,
        UpgradeSelfCheck $selfCheck,
        string $downloadPath,
        string $token
    ) {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->upgrader = $upgrader;
        $this->config = $config;
        $this->selfCheck = $selfCheck;
        $this->downloadPath = $downloadPath;
        $this->token = $token;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTemplateVars(): array
    {
        $showUpgradeButton = false;
        $showUpgradeLink = false;
        $upgradeLink = '';
        $changelogLink = '';
        $skipActions = [];
        $channel = $this->upgrader->getChannel();

        // decide to display "Start Upgrade" or not
        if ($this->selfCheck->isOkForUpgrade() && !$this->upgrader->isLastVersion()) {
            $showUpgradeButton = true;
            if ($this->upgrader->getChannel() === Upgrader::CHANNEL_ONLINE) {
                $showUpgradeLink = true;
                $upgradeLink = $this->upgrader->getOnlineDestinationRelease()->getZipDownloadUrl();
                $changelogLink = $this->upgrader->getOnlineDestinationRelease()->getReleaseNoteUrl();
            }

            // if skipActions property is used, we will handle that in the display :)
            $skipActions = AbstractTask::$skipAction;
        }

        $dir = glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.zip');
        $xml = glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.xml');

        return [
            'currentPsVersion' => _PS_VERSION_,
            'isLastVersion' => $this->upgrader->isLastVersion(),
            'destinationVersion' => $this->upgrader->getDestinationVersion(),
            'channel' => $channel,
            'showUpgradeButton' => $showUpgradeButton,
            'upgradeLink' => $upgradeLink,
            'showUpgradeLink' => $showUpgradeLink,
            'changelogLink' => $changelogLink,
            'skipActions' => $skipActions,
            'lastVersionCheck' => Configuration::get('PS_LAST_VERSION_CHECK'),
            'token' => $this->token,
            'channelOptions' => $this->getOptChannels(),
            'privateChannel' => [
                'releaseLink' => $this->config->get('private_release_link'),
                'releaseMd5' => $this->config->get('private_release_md5'),
                'allowMajor' => $this->config->get('private_allow_major'),
            ],
            'archiveFiles' => $dir,
            'xmlFiles' => $xml,
            'archiveFileName' => $this->config->getLocalChannelZip(),
            'xmlFileName' => $this->config->getLocalChannelXml(),
            'archiveVersionNumber' => $this->config->getLocalChannelVersion(),
            'downloadPath' => $this->downloadPath . DIRECTORY_SEPARATOR,
            'directoryVersionNumber' => $this->config->get('directory.version_num'),
            'phpVersion' => PHP_VERSION,
        ];
    }

    /**
     * display the summary current version / target version + "Upgrade Now" button with a "more options" button.
     *
     * @return string HTML
     */
    public function render(): string
    {
        return $this->twig->render('@ModuleAutoUpgrade/block/upgradeButtonBlock.html.twig', $this->getTemplateVars());
    }

    /**
     * @return array<int, array<string>>
     */
    private function getOptChannels(): array
    {
        $translator = $this->translator;

        return [
            ['useOnline', Upgrader::CHANNEL_ONLINE, $translator->trans('Online')],
            ['useLocal', Upgrader::CHANNEL_LOCAL, $translator->trans('Local archive')],
        ];
    }
}
