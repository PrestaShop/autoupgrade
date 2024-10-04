<?php

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

    /* step: update options */
    const UPDATE_PAGE_UPDATE_OPTIONS = 'update-page-update-options';
    const UPDATE_STEP_UPDATE_OPTIONS = 'update-step-update-options';

    /* step: backup */
    const UPDATE_PAGE_BACKUP = 'update-page-backup';
    const UPDATE_STEP_BACKUP = 'update-step-backup';

    /* step: update */
    const UPDATE_PAGE_UPDATE = 'update-page-update';
    const UPDATE_STEP_UPDATE = 'update-step-update';

    /* step: post update */
    const UPDATE_PAGE_POST_UPDATE = 'update-page-post-update';
    const UPDATE_STEP_POST_UPDATE = 'update-step-post-update';

    /* RESTORE PAGE */
    /* step: backup selection */
    const RESTORE_PAGE_BACKUP_SELECTION = 'restore-page-backup-selection';
}
