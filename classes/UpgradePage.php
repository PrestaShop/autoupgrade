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
use PrestaShop\Module\AutoUpgrade\Twig\Block\RollbackForm;
use PrestaShop\Module\AutoUpgrade\Twig\Block\UpgradeButtonBlock;
use PrestaShop\Module\AutoUpgrade\Twig\Block\UpgradeChecklist;
use PrestaShop\Module\AutoUpgrade\Twig\Form\BackupOptionsForm;
use PrestaShop\Module\AutoUpgrade\Twig\Form\FormRenderer;
use PrestaShop\Module\AutoUpgrade\Twig\Form\UpgradeOptionsForm;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Twig\Environment;

/**
 * Constructs the upgrade page.
 */
class UpgradePage
{
    const TRANSLATION_DOMAIN = 'Modules.Autoupgrade.Admin';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var UpgradeConfiguration
     */
    private $config;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var UpgradeSelfCheck
     */
    private $upgradeSelfCheck;

    /**
     * @var string
     */
    private $autoupgradePath;

    /**
     * @var Upgrader
     */
    private $upgrader;

    /**
     * @var string
     */
    private $prodRootPath;

    /**
     * @var string
     */
    private $adminPath;

    /**
     * @var string
     */
    private $currentIndex;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $backupName;

    /**
     * @var string
     */
    private $downloadPath;

    /**
     * @var BackupFinder
     */
    private $backupFinder;

    /**
     * @param Environment $twig
     */
    public function __construct(
        UpgradeConfiguration $config,
        $twig,
        Translator $translator,
        UpgradeSelfCheck $upgradeSelfCheck,
        Upgrader $upgrader,
        BackupFinder $backupFinder,
        string $autoupgradePath,
        string $prodRootPath,
        string $adminPath,
        string $currentIndex,
        string $token,
        string $backupName,
        string $downloadPath
    ) {
        $this->config = $config;
        $this->translator = $translator;
        $this->upgrader = $upgrader;
        $this->upgradeSelfCheck = $upgradeSelfCheck;
        $this->autoupgradePath = $autoupgradePath;
        $this->prodRootPath = $prodRootPath;
        $this->adminPath = $adminPath;
        $this->currentIndex = $currentIndex;
        $this->token = $token;
        $this->backupName = $backupName;
        $this->twig = $twig;
        $this->downloadPath = $downloadPath;
        $this->backupFinder = $backupFinder;
    }

    public function display(string $ajaxResult): string
    {
        $twig = $this->twig;
        $translationDomain = self::TRANSLATION_DOMAIN;

        $errMessageData = $this->getErrorMessage();
        if (!empty($errMessageData)) {
            return $twig
                ->render('@ModuleAutoUpgrade/error.html.twig', $errMessageData);
        }

        $templateData = [
            'psBaseUri' => __PS_BASE_URI__,
            'translationDomain' => $translationDomain,
            'jsParams' => $this->getJsParams($ajaxResult),
            'rollbackForm' => $this->getRollbackFormVars(),
            'backupOptions' => $this->getBackupOptionsForm(),
            'upgradeOptions' => $this->getUpgradeOptionsForm(),
            'currentIndex' => $this->currentIndex,
            'token' => $this->token,
        ];

        $templateData = array_merge(
            $templateData,
            $this->getChecklistBlockVars(),
            $this->getUpgradeButtonBlockVars(),
            $this->getRollbackFormVars()
        );

        return $twig->render('@ModuleAutoUpgrade/main.html.twig', $templateData);
    }

    /**
     * @return array<string, mixed>
     */
    private function getChecklistBlockVars(): array
    {
        return (new UpgradeChecklist(
            $this->twig,
            $this->upgradeSelfCheck,
            $this->currentIndex,
            $this->token
        ))->getTemplateVars();
    }

    /**
     * @return array<string, mixed>
     */
    private function getUpgradeButtonBlockVars(): array
    {
        return (new UpgradeButtonBlock(
            $this->twig,
            $this->translator,
            $this->config,
            $this->upgrader,
            $this->upgradeSelfCheck,
            $this->downloadPath,
            $this->token
        ))->getTemplateVars();
    }

    /**
     * @return array<string, mixed>
     */
    private function getRollbackFormVars(): array
    {
        return (new RollbackForm($this->twig, $this->backupFinder))
            ->getTemplateVars();
    }

    private function getBackupOptionsForm(): string
    {
        $formRenderer = new FormRenderer($this->config, $this->twig, $this->translator);

        return (new BackupOptionsForm($this->translator, $formRenderer))
            ->render();
    }

    private function getUpgradeOptionsForm(): string
    {
        $formRenderer = new FormRenderer($this->config, $this->twig, $this->translator);

        return (new UpgradeOptionsForm($this->translator, $formRenderer))
            ->render();
    }

    /**
     * @return array<string, string>
     */
    private function getErrorMessage(): array
    {
        $translator = $this->translator;

        // PrestaShop demo mode
        if (defined('_PS_MODE_DEMO_') && true == _PS_MODE_DEMO_) {
            return [
                'message' => $translator->trans('This functionality has been disabled.'),
            ];
        }

        if (!file_exists($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php')) {
            return [
                'message' => $translator->trans(
                    '[TECHNICAL ERROR] ajax-upgradetab.php is missing. Please reinstall or reset the module.'
                ),
            ];
        }

        return [];
    }

    /**
     * @param string $ajaxResult Json encoded response data
     *
     * @return array<string, string>
     */
    private function getJsParams(string $ajaxResult): array
    {
        // relative admin dir
        $adminDir = trim(str_replace($this->prodRootPath, '', $this->adminPath), DIRECTORY_SEPARATOR);

        $translator = $this->translator;

        return [
            'psBaseUri' => __PS_BASE_URI__,
            '_PS_MODE_DEV_' => (defined('_PS_MODE_DEV_') && true == _PS_MODE_DEV_),
            'PS_AUTOUP_BACKUP' => $this->config->shouldBackupFilesAndDatabase(),
            'adminDir' => $adminDir,
            'adminUrl' => __PS_BASE_URI__ . $adminDir,
            'token' => $this->token,
            'firstTimeParams' => json_decode($ajaxResult),
            'ajaxUpgradeTabExists' => file_exists($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php'),
            'currentIndex' => $this->currentIndex,
            'tab' => 'AdminSelfUpgrade',
            'channel' => $this->config->get('channel'),
            'autoupgrade' => [
                'version' => $this->upgradeSelfCheck->getModuleVersion(),
            ],
            'translation' => [
                'confirmDeleteBackup' => $translator->trans('Are you sure you want to delete this backup?'),
                'delete' => $translator->trans('Delete'),
                'updateInProgress' => $translator->trans('An update is currently in progress... Click "OK" to abort.'),
                'upgradingPrestaShop' => $translator->trans('Upgrading PrestaShop'),
                'upgradeComplete' => $translator->trans('Upgrade complete'),
                'upgradeCompleteWithWarnings' => $translator->trans('Upgrade complete, but warning notifications has been found.'),
                'startingRestore' => $translator->trans('Starting restoration...'),
                'restoreComplete' => $translator->trans('Restoration complete.'),
                'cannotDownloadFile' => $translator->trans('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory'),
                'jsonParseErrorForAction' => $translator->trans('Javascript error (parseJSON) detected for action '),
                'endOfProcess' => $translator->trans('End of process'),
                'processCancelledCheckForRestore' => $translator->trans('Operation canceled. Checking for restoration...'),
                'confirmRestoreBackup' => $translator->trans('Do you want to restore %s?', [$this->backupName]),
                'processCancelledWithError' => $translator->trans('Operation canceled. An error happened.'),
                'missingAjaxUpgradeTab' => $translator->trans('[TECHNICAL ERROR] ajax-upgradetab.php is missing. Please reinstall the module.'),
                'clickToRefreshAndUseNewConfiguration' => $translator->trans('Click to refresh the page and use the new configuration'),
                'errorDetectedDuring' => $translator->trans('Error detected during'),
                'downloadTimeout' => $translator->trans('The request exceeded the max_time_limit. Please change your server configuration.'),
                'seeOrHideList' => $translator->trans('See or hide the list'),
                'coreFiles' => $translator->trans('Core file(s)'),
                'mailFiles' => $translator->trans('Mail file(s)'),
                'translationFiles' => $translator->trans('Translation file(s)'),
                'linkAndMd5CannotBeEmpty' => $translator->trans('Link and MD5 hash cannot be empty'),
                'needToEnterArchiveVersionNumber' => $translator->trans('You need to enter the version number associated with the archive.'),
                'noArchiveSelected' => $translator->trans('No archive has been selected.'),
                'needToEnterDirectoryVersionNumber' => $translator->trans('You need to enter the version number associated with the directory.'),
                'confirmSkipBackup' => $translator->trans('Please confirm that you want to skip the backup.'),
                'confirmPreserveFileOptions' => $translator->trans('Please confirm that you want to preserve file options.'),
                'lessOptions' => $translator->trans('Less options'),
                'moreOptions' => $translator->trans('More options (Expert mode)'),
                'filesWillBeDeleted' => $translator->trans('These files will be deleted'),
                'filesWillBeReplaced' => $translator->trans('These files will be replaced'),
                'noXmlSelected' => $translator->trans('No XML file has been selected.'),
                'noArchiveAndXmlSelected' => $translator->trans('No archive and no XML file have been selected.'),
            ],
        ];
    }
}
