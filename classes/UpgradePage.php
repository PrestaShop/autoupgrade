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
use Twig_Environment;

/**
 * Constructs the upgrade page.
 */
class UpgradePage
{
    const TRANSLATION_DOMAIN = 'Modules.Autoupgrade.Admin';

    /**
     * @var string
     */
    private $moduleDir;

    /**
     * @var string
     */
    private $templatesDir = '/views/templates';

    /**
     * @var Twig_Environment|\Twig\Environment
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

    public function __construct(
        UpgradeConfiguration $config,
        $twig,
        Translator $translator,
        UpgradeSelfCheck $upgradeSelfCheck,
        Upgrader $upgrader,
        BackupFinder $backupFinder,
        $autoupgradePath,
        $prodRootPath,
        $adminPath,
        $currentIndex,
        $token,
        $installVersion,
        $manualMode,
        $backupName,
        $downloadPath
    ) {
        $this->moduleDir = realpath(__DIR__ . '/../');
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

    /**
     * Renders the page.
     *
     * @return string HTML
     */
    public function display($ajaxResult)
    {
        $twig = $this->twig;
        $translationDomain = self::TRANSLATION_DOMAIN;

        $errMessageData = $this->getErrorMessage();
        if (!empty($errMessageData)) {
            return $twig
                ->render('@ModuleAutoUpgrade:error.twig', $errMessageData);
        }

        $templateData = [
            'psBaseUri' => __PS_BASE_URI__,
            'translationDomain' => $translationDomain,
            'jsParams' => $this->getJsParams($ajaxResult),
            'currentConfig' => $this->getChecklistBlock(),
            'upgradeButtonBlock' => $this->getUpgradeButtonBlock(),
            'rollbackForm' => $this->getRollbackForm(),
            'backupOptions' => $this->getBackupOptionsForm(),
            'upgradeOptions' => $this->getUpgradeOptionsForm(),
            'currentIndex' => $this->currentIndex,
            'token' => $this->token,
        ];

        return $twig->render('@ModuleAutoUpgrade/main.twig', $templateData);
    }

    /**
     * @return string HTML
     */
    private function getChecklistBlock()
    {
        return (new UpgradeChecklist(
            $this->twig,
            $this->upgradeSelfCheck,
            $this->prodRootPath,
            $this->adminPath,
            $this->autoupgradePath,
            $this->currentIndex,
            $this->token
        ))->render();
    }

    /**
     * @return string HTML
     */
    private function getUpgradeButtonBlock()
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
        ))->render();
    }

    /**
     * @return string
     */
    private function getRollbackForm()
    {
        return (new RollbackForm($this->twig, $this->backupFinder))
            ->render();
    }

    /**
     * @return string
     */
    private function getBackupOptionsForm()
    {
        $formRenderer = new FormRenderer($this->config, $this->twig, $this->translator);

        return (new BackupOptionsForm($this->translator, $formRenderer))
            ->render();
    }

    /**
     * @return string
     */
    private function getUpgradeOptionsForm()
    {
        $formRenderer = new FormRenderer($this->config, $this->twig, $this->translator);

        return (new UpgradeOptionsForm($this->translator, $formRenderer))
            ->render();
    }

    /**
     * @return array
     */
    private function getErrorMessage()
    {
        $translator = $this->translator;

        // PrestaShop demo mode
        if (defined('_PS_MODE_DEMO_') && true == _PS_MODE_DEMO_) {
            return [
                'message' => $translator->trans('This functionality has been disabled.', [], 'Modules.Autoupgrade.Admin'),
            ];
        }

        if (!file_exists($this->autoupgradePath . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php')) {
            return [
                'showWarningIcon' => true,
                'message' => $translator->trans(
                    '[TECHNICAL ERROR] ajax-upgradetab.php is missing. Please reinstall or reset the module.',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
            ];
        }

        return [];
    }

    /**
     * @param string $ajaxResult Json encoded response data
     *
     * @return array
     */
    private function getJsParams($ajaxResult)
    {
        $translationDomain = self::TRANSLATION_DOMAIN;
        // relative admin dir
        $adminDir = trim(str_replace($this->prodRootPath, '', $this->adminPath), DIRECTORY_SEPARATOR);

        $translator = $this->translator;

        $jsParams = [
            'manualMode' => (bool) $this->manualMode,
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
                'confirmDeleteBackup' => $translator->trans('Are you sure you want to delete this backup?', [], 'Modules.Autoupgrade.Admin'),
                'delete' => $translator->trans('Delete', [], 'Admin.Actions'),
                'updateInProgress' => $translator->trans('An update is currently in progress... Click "OK" to abort.', [], 'Modules.Autoupgrade.Admin'),
                'upgradingPrestaShop' => $translator->trans('Upgrading PrestaShop', [], 'Modules.Autoupgrade.Admin'),
                'upgradeComplete' => $translator->trans('Upgrade complete', [], 'Modules.Autoupgrade.Admin'),
                'upgradeCompleteWithWarnings' => $translator->trans('Upgrade complete, but warning notifications has been found.', [], 'Modules.Autoupgrade.Admin'),
                'todoList' => [
                    $translator->trans('Cookies have changed, you will need to log in again once you refreshed the page', [], 'Modules.Autoupgrade.Admin'),
                    $translator->trans('Javascript and CSS files have changed, please clear your browser cache with CTRL-F5', [], 'Modules.Autoupgrade.Admin'),
                    $translator->trans('Please check that your front-office theme is functional (try to create an account, place an order...)', [], 'Modules.Autoupgrade.Admin'),
                    $translator->trans('Product images do not appear in the front-office? Try regenerating the thumbnails in Preferences > Images', [], 'Modules.Autoupgrade.Admin'),
                    $translator->trans('Do not forget to reactivate your shop once you have checked everything!', [], 'Modules.Autoupgrade.Admin'),
                ],
                'todoListTitle' => $translator->trans('ToDo list:', [], 'Modules.Autoupgrade.Admin'),
                'startingRestore' => $translator->trans('Starting restoration...', [], 'Modules.Autoupgrade.Admin'),
                'restoreComplete' => $translator->trans('Restoration complete.', [], 'Modules.Autoupgrade.Admin'),
                'cannotDownloadFile' => $translator->trans('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory', [], 'Modules.Autoupgrade.Admin'),
                'jsonParseErrorForAction' => $translator->trans('Javascript error (parseJSON) detected for action ', [], 'Modules.Autoupgrade.Admin'),
                'manuallyGoToButton' => $translator->trans('Manually go to %s button', [], 'Modules.Autoupgrade.Admin'),
                'endOfProcess' => $translator->trans('End of process', [], 'Modules.Autoupgrade.Admin'),
                'processCancelledCheckForRestore' => $translator->trans('Operation canceled. Checking for restoration...', [], 'Modules.Autoupgrade.Admin'),
                'confirmRestoreBackup' => $translator->trans('Do you want to restore %s?', [$this->backupName], 'Modules.Autoupgrade.Admin'),
                'processCancelledWithError' => $translator->trans('Operation canceled. An error happened.', [], 'Modules.Autoupgrade.Admin'),
                'missingAjaxUpgradeTab' => $translator->trans('[TECHNICAL ERROR] ajax-upgradetab.php is missing. Please reinstall the module.', [], 'Modules.Autoupgrade.Admin'),
                'clickToRefreshAndUseNewConfiguration' => $translator->trans('Click to refresh the page and use the new configuration', [], 'Modules.Autoupgrade.Admin'),
                'errorDetectedDuring' => $translator->trans('Error detected during', [], 'Modules.Autoupgrade.Admin'),
                'downloadTimeout' => $translator->trans('The request exceeded the max_time_limit. Please change your server configuration.', [], 'Modules.Autoupgrade.Admin'),
                'seeOrHideList' => $translator->trans('See or hide the list', [], 'Modules.Autoupgrade.Admin'),
                'coreFiles' => $translator->trans('Core file(s)', [], 'Modules.Autoupgrade.Admin'),
                'mailFiles' => $translator->trans('Mail file(s)', [], 'Modules.Autoupgrade.Admin'),
                'translationFiles' => $translator->trans('Translation file(s)', [], 'Modules.Autoupgrade.Admin'),
                'linkAndMd5CannotBeEmpty' => $translator->trans('Link and MD5 hash cannot be empty', [], 'Modules.Autoupgrade.Admin'),
                'needToEnterArchiveVersionNumber' => $translator->trans('You need to enter the version number associated with the archive.', [], 'Modules.Autoupgrade.Admin'),
                'noArchiveSelected' => $translator->trans('No archive has been selected.', [], 'Modules.Autoupgrade.Admin'),
                'needToEnterDirectoryVersionNumber' => $translator->trans('You need to enter the version number associated with the directory.', [], 'Modules.Autoupgrade.Admin'),
                'confirmSkipBackup' => $translator->trans('Please confirm that you want to skip the backup.', [], 'Modules.Autoupgrade.Admin'),
                'confirmPreserveFileOptions' => $translator->trans('Please confirm that you want to preserve file options.', [], 'Modules.Autoupgrade.Admin'),
                'lessOptions' => $translator->trans('Less options', [], 'Modules.Autoupgrade.Admin'),
                'moreOptions' => $translator->trans('More options (Expert mode)', [], 'Modules.Autoupgrade.Admin'),
                'filesWillBeDeleted' => $translator->trans('These files will be deleted', [], 'Modules.Autoupgrade.Admin'),
                'filesWillBeReplaced' => $translator->trans('These files will be replaced', [], 'Modules.Autoupgrade.Admin'),
            ],
        ];

        return $jsParams;
    }

    /**
     * @return array
     */
    private function _getJsErrorMsgs()
    {
        $translationDomain = self::TRANSLATION_DOMAIN;
        $translator = $this->translator;
        $ret = [
            0 => $translator->trans('Required field', [], 'Modules.Autoupgrade.Admin'),
            1 => $translator->trans('Too long!', [], 'Modules.Autoupgrade.Admin'),
            2 => $translator->trans('Fields are different!', [], 'Modules.Autoupgrade.Admin'),
            3 => $translator->trans('This email address is wrong!', [], 'Modules.Autoupgrade.Admin'),
            4 => $translator->trans('Impossible to send the email!', [], 'Modules.Autoupgrade.Admin'),
            5 => $translator->trans('Cannot create settings file, if /app/config/parameters.php exists, please give the public write permissions to this file, else please create a file named parameters.php in config directory.', [], 'Modules.Autoupgrade.Admin'),
            6 => $translator->trans('Cannot write settings file, please create a file named settings.inc.php in the "config" directory.', [], 'Modules.Autoupgrade.Admin'),
            7 => $translator->trans('Impossible to upload the file!', [], 'Modules.Autoupgrade.Admin'),
            8 => $translator->trans('Data integrity is not valid. Hack attempt?', [], 'Modules.Autoupgrade.Admin'),
            9 => $translator->trans('Impossible to read the content of a MySQL content file.', [], 'Modules.Autoupgrade.Admin'),
            10 => $translator->trans('Cannot access a MySQL content file.', [], 'Modules.Autoupgrade.Admin'),
            11 => $translator->trans('Error while inserting data in the database:', [], 'Modules.Autoupgrade.Admin'),
            12 => $translator->trans('The password is incorrect (must be alphanumeric string with at least 8 characters)', [], 'Install'),
            14 => $translator->trans('At least one table with same prefix was already found, please change your prefix or drop your database', [], 'Install'),
            15 => $translator->trans('This is not a valid file name.', [], 'Modules.Autoupgrade.Admin'),
            16 => $translator->trans('This is not a valid image file.', [], 'Modules.Autoupgrade.Admin'),
            17 => $translator->trans('Error while creating the /app/config/parameters.php file.', [], 'Modules.Autoupgrade.Admin'),
            18 => $translator->trans('Error:', [], 'Modules.Autoupgrade.Admin'),
            19 => $translator->trans('This PrestaShop database already exists. Please revalidate your authentication information to the database.', [], 'Modules.Autoupgrade.Admin'),
            22 => $translator->trans('An error occurred while resizing the picture.', [], 'Modules.Autoupgrade.Admin'),
            23 => $translator->trans('Database connection is available!', [], 'Modules.Autoupgrade.Admin'),
            24 => $translator->trans('Database Server is available but database is not found', [], 'Modules.Autoupgrade.Admin'),
            25 => $translator->trans('Database Server is not found. Please verify the login, password and server fields.', [], 'Modules.Autoupgrade.Admin'),
            26 => $translator->trans('An error occurred while sending email, please verify your parameters.', [], 'Modules.Autoupgrade.Admin'),
            // Upgrader
            27 => $translator->trans('This installer is too old.', [], 'Modules.Autoupgrade.Admin'),
            28 => $translator->trans('You already have the %s version.', [$this->installVersion], 'Modules.Autoupgrade.Admin'),
            29 => $translator->trans('There is no older version. Did you delete or rename the app/config/parameters.php file?', [], 'Modules.Autoupgrade.Admin'),
            30 => $translator->trans('The app/config/parameters.php file was not found. Did you delete or rename this file?', [], 'Modules.Autoupgrade.Admin'),
            31 => $translator->trans('Cannot find the SQL upgrade files. Please verify that the /install/upgrade/sql folder is not empty.', [], 'Modules.Autoupgrade.Admin'),
            32 => $translator->trans('No upgrade is possible.', [], 'Modules.Autoupgrade.Admin'),
            33 => $translator->trans('Error while loading SQL upgrade file.', [], 'Modules.Autoupgrade.Admin'),
            34 => $translator->trans('Error while inserting content into the database', [], 'Modules.Autoupgrade.Admin'),
            35 => $translator->trans('Unfortunately,', [], 'Modules.Autoupgrade.Admin'),
            36 => $translator->trans('SQL errors have occurred.', [], 'Modules.Autoupgrade.Admin'),
            37 => $translator->trans('The config/defines.inc.php file was not found. Where did you move it?', [], 'Modules.Autoupgrade.Admin'),
            // End of upgrader
            38 => $translator->trans('Impossible to write the image /img/logo.jpg. If this image already exists, please delete it.', [], 'Modules.Autoupgrade.Admin'),
            39 => $translator->trans('The uploaded file exceeds the upload_max_filesize directive in php.ini', [], 'Modules.Autoupgrade.Admin'),
            40 => $translator->trans('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', [], 'Modules.Autoupgrade.Admin'),
            41 => $translator->trans('The uploaded file was only partially uploaded', [], 'Modules.Autoupgrade.Admin'),
            42 => $translator->trans('No file was uploaded.', [], 'Modules.Autoupgrade.Admin'),
            43 => $translator->trans('Missing a temporary folder', [], 'Modules.Autoupgrade.Admin'),
            44 => $translator->trans('Failed to write file to disk', [], 'Modules.Autoupgrade.Admin'),
            45 => $translator->trans('File upload stopped by extension', [], 'Modules.Autoupgrade.Admin'),
            46 => $translator->trans('Cannot convert your database\'s data to utf-8.', [], 'Modules.Autoupgrade.Admin'),
            47 => $translator->trans('Invalid shop name', [], 'Install'),
            48 => $translator->trans('Your firstname contains some invalid characters', [], 'Install'),
            49 => $translator->trans('Your lastname contains some invalid characters', [], 'Modules.Autoupgrade.Admin'),
            50 => $translator->trans('Your database server does not support the utf-8 charset.', [], 'Install'),
            51 => $translator->trans('Your MySQL server does not support this engine, please use another one like MyISAM', [], 'Modules.Autoupgrade.Admin'),
            52 => $translator->trans('The file /img/logo.jpg is not writable, please CHMOD 755 this file or CHMOD 777', [], 'Modules.Autoupgrade.Admin'),
            53 => $translator->trans('Invalid catalog mode', [], 'Modules.Autoupgrade.Admin'),
            999 => $translator->trans('No error code available', [], 'Modules.Autoupgrade.Admin'),
        ];

        return $ret;
    }
}
