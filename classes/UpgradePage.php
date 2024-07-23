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
    private $installVersion;

    /**
     * @var bool
     */
    private $manualMode;

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
        string $installVersion,
        bool $manualMode,
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
        $this->installVersion = $installVersion;
        $this->manualMode = $manualMode;
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
            $this->token,
            $this->manualMode
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
            'manualMode' => $this->manualMode,
            '_PS_MODE_DEV_' => (defined('_PS_MODE_DEV_') && true == _PS_MODE_DEV_),
            'PS_AUTOUP_BACKUP' => (bool) $this->config->get('PS_AUTOUP_BACKUP'),
            'adminDir' => $adminDir,
            'adminUrl' => __PS_BASE_URI__ . $adminDir,
            'token' => $this->token,
            'txtError' => $this->_getJsErrorMsgs(),
            'firstTimeParams' => json_decode($ajaxResult),
            'ajaxUpgradeTabExists' => file_exists($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php'),
            'currentIndex' => $this->currentIndex,
            'tab' => 'AdminSelfUpgrade',
            'channel' => $this->config->get('channel'),
            'translation' => [
                'confirmDeleteBackup' => $translator->trans('Are you sure you want to delete this backup?'),
                'delete' => $translator->trans('Delete'),
                'updateInProgress' => $translator->trans('An update is currently in progress... Click "OK" to abort.'),
                'upgradingPrestaShop' => $translator->trans('Upgrading PrestaShop'),
                'upgradeComplete' => $translator->trans('Upgrade complete'),
                'upgradeCompleteWithWarnings' => $translator->trans('Upgrade complete, but warning notifications has been found.'),
                'todoList' => [
                    $translator->trans('Cookies have changed, you will need to log in again once you refreshed the page'),
                    $translator->trans('Javascript and CSS files have changed, please clear your browser cache with CTRL-F5'),
                    $translator->trans('Please check that your front-office theme is functional (try to create an account, place an order...)'),
                    $translator->trans('Product images do not appear in the front-office? Try regenerating the thumbnails in Preferences > Images'),
                    $translator->trans('Do not forget to reactivate your shop once you have checked everything!'),
                    $translator->trans('If you can\'t access the back-office and need to see what\'s wrong, manually enable debug mode in config/defines.inc.php by changing _PS_MODE_DEV_ to true.'),
                ],
                'todoListTitle' => $translator->trans('ToDo list:'),
                'startingRestore' => $translator->trans('Starting restoration...'),
                'restoreComplete' => $translator->trans('Restoration complete.'),
                'cannotDownloadFile' => $translator->trans('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory'),
                'jsonParseErrorForAction' => $translator->trans('Javascript error (parseJSON) detected for action '),
                'manuallyGoToButton' => $translator->trans('Manually go to %s button'),
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
            ],
        ];
    }

    /**
     * @return string[]
     */
    private function _getJsErrorMsgs(): array
    {
        $translator = $this->translator;

        return [
            0 => $translator->trans('Required field'),
            1 => $translator->trans('Too long!'),
            2 => $translator->trans('Fields are different!'),
            3 => $translator->trans('This email address is wrong!'),
            4 => $translator->trans('Impossible to send the email!'),
            5 => $translator->trans('Cannot create settings file, if /app/config/parameters.php exists, please give the public write permissions to this file, else please create a file named parameters.php in config directory.'),
            6 => $translator->trans('Cannot write settings file, please create a file named settings.inc.php in the "config" directory.'),
            7 => $translator->trans('Impossible to upload the file!'),
            8 => $translator->trans('Data integrity is not valid, the files might have been corrupted, or a hack attempt might have occured.'),
            9 => $translator->trans('Impossible to read the content of a MySQL content file.'),
            10 => $translator->trans('Cannot access a MySQL content file.'),
            11 => $translator->trans('Error while inserting data in the database:'),
            12 => $translator->trans('The password is incorrect (must be alphanumeric string with at least 8 characters)'),
            14 => $translator->trans('At least one table with same prefix was already found, please change your prefix or drop your database'),
            15 => $translator->trans('This is not a valid file name.'),
            16 => $translator->trans('This is not a valid image file.'),
            17 => $translator->trans('Error while creating the /app/config/parameters.php file.'),
            18 => $translator->trans('Error:'),
            19 => $translator->trans('This PrestaShop database already exists. Please revalidate your authentication information to the database.'),
            22 => $translator->trans('An error occurred while resizing the picture.'),
            23 => $translator->trans('Database connection is available!'),
            24 => $translator->trans('Database Server is available but database is not found'),
            25 => $translator->trans('Database Server is not found. Please verify the login, password and server fields.'),
            26 => $translator->trans('An error occurred while sending email, please verify your parameters.'),
            // Upgrader
            27 => $translator->trans('This installer is too old.'),
            28 => $translator->trans('You already have the %s version.', [$this->installVersion]),
            29 => $translator->trans('There is no older version. Did you delete or rename the app/config/parameters.php file?'),
            30 => $translator->trans('The app/config/parameters.php file was not found. Did you delete or rename this file?'),
            31 => $translator->trans('Cannot find the SQL upgrade files. Please verify that the /install/upgrade/sql folder is not empty.'),
            32 => $translator->trans('No upgrade is possible.'),
            33 => $translator->trans('Error while loading SQL upgrade file.'),
            34 => $translator->trans('Error while inserting content into the database'),
            35 => $translator->trans('Unfortunately,'),
            36 => $translator->trans('SQL errors have occurred.'),
            37 => $translator->trans('The config/defines.inc.php file was not found. Where did you move it?'),
            // End of upgrader
            38 => $translator->trans('Impossible to write the image /img/logo.jpg. If this image already exists, please delete it.'),
            39 => $translator->trans('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
            40 => $translator->trans('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
            41 => $translator->trans('The uploaded file was only partially uploaded'),
            42 => $translator->trans('No file was uploaded.'),
            43 => $translator->trans('Missing a temporary folder'),
            44 => $translator->trans('Failed to write file to disk'),
            45 => $translator->trans('File upload stopped by extension'),
            46 => $translator->trans('Cannot convert your database\'s data to utf-8.'),
            47 => $translator->trans('Invalid shop name'),
            48 => $translator->trans('Your firstname contains some invalid characters'),
            49 => $translator->trans('Your lastname contains some invalid characters'),
            50 => $translator->trans('Your database server does not support the utf-8 charset.'),
            51 => $translator->trans('Your MySQL server does not support this engine, please use another one like MyISAM'),
            52 => $translator->trans('The file /img/logo.jpg is not writable, please CHMOD 755 this file or CHMOD 777'),
            53 => $translator->trans('Invalid catalog mode'),
            999 => $translator->trans('No error code available'),
        ];
    }
}
