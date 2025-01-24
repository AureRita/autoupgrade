<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

class Routes
{
    /* HOME PAGE */
    /** @var string */
    const HOME_PAGE = 'home-page';
    /** @var string */
    const HOME_PAGE_SUBMIT_FORM = 'home-page-submit-form';

    /* UPDATE PAGE */
    /* step: version choice */
    /** @var string */
    const UPDATE_PAGE_VERSION_CHOICE = 'update-page-version-choice';
    /** @var string */
    const UPDATE_STEP_VERSION_CHOICE = 'update-step-version-choice';
    /** @var string */
    const UPDATE_STEP_VERSION_CHOICE_SAVE_FORM = 'update-step-version-choice-save-form';
    /** @var string */
    const UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM = 'update-step-version-choice-submit-form';
    /** @var string */
    const UPDATE_STEP_VERSION_CHOICE_CORE_TEMPERED_FILES_DIALOG = 'update-step-version-choice-core-tempered-files-dialog';
    /** @var string */
    const UPDATE_STEP_VERSION_CHOICE_THEME_TEMPERED_FILES_DIALOG = 'update-step-version-choice-theme-tempered-files-dialog';

    /* step: update options */
    /** @var string */
    const UPDATE_PAGE_UPDATE_OPTIONS = 'update-page-update-options';
    /** @var string */
    const UPDATE_STEP_UPDATE_OPTIONS = 'update-step-update-options';
    /** @var string */
    const UPDATE_STEP_UPDATE_OPTIONS_SAVE_OPTION = 'update-step-update-options-save-option';
    /** @var string */
    const UPDATE_STEP_UPDATE_OPTIONS_SUBMIT_FORM = 'update-step-update-options-submit-form';

    /* step: backup */
    /** @var string */
    const UPDATE_PAGE_BACKUP_OPTIONS = 'update-page-backup-options';
    /** @var string */
    const UPDATE_STEP_BACKUP_OPTIONS = 'update-step-backup-options';
    /** @var string */
    const UPDATE_STEP_BACKUP_SAVE_OPTION = 'update-step-backup-save-option';
    /** @var string */
    const UPDATE_STEP_BACKUP_SUBMIT_BACKUP = 'update-step-backup-submit-backup';
    /** @var string */
    const UPDATE_STEP_BACKUP_SUBMIT_UPDATE = 'update-step-backup-submit-update';
    /** @var string */
    const UPDATE_STEP_BACKUP_CONFIRM_BACKUP = 'update-step-backup-confirm-backup';
    /** @var string */
    const UPDATE_STEP_BACKUP_CONFIRM_UPDATE = 'update-step-backup-confirm-update';

    /** @var string */
    const UPDATE_PAGE_BACKUP = 'update-page-backup';
    /** @var string */
    const UPDATE_STEP_BACKUP = 'update-step-backup';

    /* step: update */
    /** @var string */
    const UPDATE_PAGE_UPDATE = 'update-page-update';
    /** @var string */
    const UPDATE_STEP_UPDATE = 'update-step-update';

    /* step: post update */
    /** @var string */
    const UPDATE_PAGE_POST_UPDATE = 'update-page-post-update';
    /** @var string */
    const UPDATE_STEP_POST_UPDATE = 'update-step-post-update';

    /* RESTORE PAGE */
    /* step: backup selection */
    /** @var string */
    const RESTORE_PAGE_BACKUP_SELECTION = 'restore-page-backup-selection';
    /** @var string */
    const RESTORE_STEP_BACKUP_SELECTION = 'restore-step-backup-selection';
    /** @var string */
    const RESTORE_STEP_BACKUP_SELECTION_SAVE_FORM = 'restore-step-backup-selection-save-form';
    /** @var string */
    const RESTORE_STEP_BACKUP_SELECTION_CONFIRM_RESTORE_FORM = 'restore-step-backup-selection-confirm-restore-form';
    /** @var string */
    const RESTORE_STEP_BACKUP_SELECTION_SUBMIT_RESTORE_FORM = 'restore-step-backup-selection-submit-restore-form';
    /** @var string */
    const RESTORE_STEP_BACKUP_SELECTION_CONFIRM_DELETE_FORM = 'restore-step-backup-selection-confirm-delete-form';
    /** @var string */
    const RESTORE_STEP_BACKUP_SELECTION_SUBMIT_DELETE_FORM = 'restore-step-backup-selection-submit-delete-form';

    /* step: restore */
    /** @var string */
    const RESTORE_PAGE_RESTORE = 'restore-page-restore';
    /** @var string */
    const RESTORE_STEP_RESTORE = 'restore-step-restore';

    /* step: post restore */
    /** @var string */
    const RESTORE_PAGE_POST_RESTORE = 'restore-page-post-restore';
    /** @var string */
    const RESTORE_STEP_POST_RESTORE = 'restore-step-post-restore';

    /* COMMON */
    /* error reporting */
    /** @var string */
    const DISPLAY_ERROR_REPORT_MODAL = 'update-step-update-submit-error-report';

    /* logs */
    /** @var string */
    const DOWNLOAD_LOGS = 'download-logs';

    /* errors */
    /** @var string */
    const ERROR_404 = 'error-404';
}
