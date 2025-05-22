<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Router;

class Routes
{
    /* HOME PAGE */
    const HOME_PAGE = 'home-page';
    const HOME_PAGE_SUBMIT_FORM = 'home-page-submit-form';

    /* UPDATE PAGE */
    /* step: version choice */
    const UPDATE_PAGE_VERSION_CHOICE = 'update-page-version-choice';
    const UPDATE_STEP_VERSION_CHOICE = 'update-step-version-choice';
    const UPDATE_STEP_VERSION_CHOICE_SAVE_FORM = 'update-step-version-choice-save-form';
    const UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM = 'update-step-version-choice-submit-form';
    const UPDATE_STEP_VERSION_CHOICE_CORE_TEMPERED_FILES_DIALOG = 'update-step-version-choice-core-tempered-files-dialog';
    const UPDATE_STEP_VERSION_CHOICE_CORE_TEMPERED_FILES_CONTENT = 'update-step-version-choice-core-tempered-files-content';
    const UPDATE_STEP_VERSION_CHOICE_THEME_TEMPERED_FILES_DIALOG = 'update-step-version-choice-theme-tempered-files-dialog';
    const UPDATE_STEP_VERSION_CHOICE_THEME_TEMPERED_FILES_CONTENT = 'update-step-version-choice-theme-tempered-files-content';

    /* step: update options */
    const UPDATE_PAGE_UPDATE_OPTIONS = 'update-page-update-options';
    const UPDATE_STEP_UPDATE_OPTIONS = 'update-step-update-options';
    const UPDATE_STEP_UPDATE_OPTIONS_SAVE_OPTION = 'update-step-update-options-save-option';
    const UPDATE_STEP_UPDATE_OPTIONS_SUBMIT_FORM = 'update-step-update-options-submit-form';

    /* step: backup */
    const UPDATE_PAGE_BACKUP_OPTIONS = 'update-page-backup-options';
    const UPDATE_STEP_BACKUP_OPTIONS = 'update-step-backup-options';
    const UPDATE_STEP_BACKUP_SAVE_OPTION = 'update-step-backup-save-option';
    const UPDATE_STEP_BACKUP_SUBMIT_BACKUP = 'update-step-backup-submit-backup';
    const UPDATE_STEP_BACKUP_SUBMIT_UPDATE = 'update-step-backup-submit-update';
    const UPDATE_STEP_BACKUP_CONFIRM_BACKUP = 'update-step-backup-confirm-backup';
    const UPDATE_STEP_BACKUP_CONFIRM_UPDATE = 'update-step-backup-confirm-update';

    const UPDATE_PAGE_BACKUP = 'update-page-backup';
    const UPDATE_STEP_BACKUP = 'update-step-backup';

    /* step: update */
    const UPDATE_PAGE_UPDATE = 'update-page-update';
    const UPDATE_STEP_UPDATE = 'update-step-update';

    /* step: post update */
    const UPDATE_PAGE_POST_UPDATE = 'update-page-post-update';
    const UPDATE_STEP_POST_UPDATE = 'update-step-post-update';
    const UPDATE_STEP_POST_UPDATE_CONFIRM_MODULE_MANAGER_DIALOG = 'update-step-post-update-confirm-module-manager-dialog';

    /* RESTORE PAGE */
    /* step: backup selection */
    const RESTORE_PAGE_BACKUP_SELECTION = 'restore-page-backup-selection';
    const RESTORE_STEP_BACKUP_SELECTION = 'restore-step-backup-selection';
    const RESTORE_STEP_BACKUP_SELECTION_SAVE_FORM = 'restore-step-backup-selection-save-form';
    const RESTORE_STEP_BACKUP_SELECTION_CONFIRM_RESTORE_FORM = 'restore-step-backup-selection-confirm-restore-form';
    const RESTORE_STEP_BACKUP_SELECTION_SUBMIT_RESTORE_FORM = 'restore-step-backup-selection-submit-restore-form';
    const RESTORE_STEP_BACKUP_SELECTION_CONFIRM_DELETE_FORM = 'restore-step-backup-selection-confirm-delete-form';
    const RESTORE_STEP_BACKUP_SELECTION_SUBMIT_DELETE_FORM = 'restore-step-backup-selection-submit-delete-form';

    /* step: restore */
    const RESTORE_PAGE_RESTORE = 'restore-page-restore';
    const RESTORE_STEP_RESTORE = 'restore-step-restore';

    /* step: post restore */
    const RESTORE_PAGE_POST_RESTORE = 'restore-page-post-restore';
    const RESTORE_STEP_POST_RESTORE = 'restore-step-post-restore';

    /* COMMON */
    /* error reporting */
    const DISPLAY_ERROR_REPORT_MODAL = 'update-step-update-submit-error-report';

    /* logs */
    const DOWNLOAD_LOGS = 'download-logs';

    /* errors */
    const ERROR_404 = 'error-404';
}
