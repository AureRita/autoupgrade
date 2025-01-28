<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\Controller\Error404Controller;
use PrestaShop\Module\AutoUpgrade\Controller\ErrorReportController;
use PrestaShop\Module\AutoUpgrade\Controller\HomePageController;
use PrestaShop\Module\AutoUpgrade\Controller\LogsController;
use PrestaShop\Module\AutoUpgrade\Controller\RestorePageBackupSelectionController;
use PrestaShop\Module\AutoUpgrade\Controller\RestorePagePostRestoreController;
use PrestaShop\Module\AutoUpgrade\Controller\RestorePageRestoreController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageBackupController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageBackupOptionsController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePagePostUpdateController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageUpdateController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageUpdateOptionsController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageVersionChoiceController;
use PrestaShop\Module\AutoUpgrade\Router\Middlewares\LocalChannelXmlAndZipExist;
use PrestaShop\Module\AutoUpgrade\Router\Middlewares\UpdateIsConfigured;

class RoutesConfig
{
    const ROUTES =
        /* HOME PAGE */
        [
            Routes::HOME_PAGE => [
                'controller' => HomePageController::class,
                'method' => 'index',
            ],
            Routes::HOME_PAGE_SUBMIT_FORM => [
                'controller' => HomePageController::class,
                'method' => 'submit',
            ],
            /* UPDATE PAGE */
            /* step: version choice */
            Routes::UPDATE_PAGE_VERSION_CHOICE => [
                'controller' => UpdatePageVersionChoiceController::class,
                'method' => 'index',
            ],
            Routes::UPDATE_STEP_VERSION_CHOICE => [
                'controller' => UpdatePageVersionChoiceController::class,
                'method' => 'step',
            ],
            Routes::UPDATE_STEP_VERSION_CHOICE_SAVE_FORM => [
                'controller' => UpdatePageVersionChoiceController::class,
                'method' => 'save',
            ],
            Routes::UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM => [
                'controller' => UpdatePageVersionChoiceController::class,
                'method' => 'submit',
            ],
            Routes::UPDATE_STEP_VERSION_CHOICE_CORE_TEMPERED_FILES_DIALOG => [
                'controller' => UpdatePageVersionChoiceController::class,
                'method' => 'coreTemperedFilesDialog',
            ],
            Routes::UPDATE_STEP_VERSION_CHOICE_THEME_TEMPERED_FILES_DIALOG => [
                'controller' => UpdatePageVersionChoiceController::class,
                'method' => 'themeTemperedFilesDialog',
            ],
            /* step: update options */
            Routes::UPDATE_PAGE_UPDATE_OPTIONS => [
                'controller' => UpdatePageUpdateOptionsController::class,
                'method' => 'index',
                'middleware' => [
                    UpdateIsConfigured::class,
                    LocalChannelXmlAndZipExist::class,
                ],
            ],
            Routes::UPDATE_STEP_UPDATE_OPTIONS => [
                'controller' => UpdatePageUpdateOptionsController::class,
                'method' => 'step',
            ],
            Routes::UPDATE_STEP_UPDATE_OPTIONS_SAVE_OPTION => [
                'controller' => UpdatePageUpdateOptionsController::class,
                'method' => 'saveOption',
            ],
            Routes::UPDATE_STEP_UPDATE_OPTIONS_SUBMIT_FORM => [
                'controller' => UpdatePageUpdateOptionsController::class,
                'method' => 'submit',
            ],
            /* step: backup */
            Routes::UPDATE_PAGE_BACKUP_OPTIONS => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'index',
                'middleware' => [
                    UpdateIsConfigured::class,
                    LocalChannelXmlAndZipExist::class,
                ],
            ],
            Routes::UPDATE_STEP_BACKUP_OPTIONS => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'step',
            ],
            Routes::UPDATE_STEP_BACKUP_SAVE_OPTION => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'saveOption',
            ],
            Routes::UPDATE_STEP_BACKUP_SUBMIT_BACKUP => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'submitBackup',
            ],
            Routes::UPDATE_STEP_BACKUP_SUBMIT_UPDATE => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'submitUpdate',
            ],
            Routes::UPDATE_STEP_BACKUP_CONFIRM_BACKUP => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'startBackup',
            ],
            Routes::UPDATE_STEP_BACKUP_CONFIRM_UPDATE => [
                'controller' => UpdatePageBackupOptionsController::class,
                'method' => 'startUpdate',
            ],
            Routes::UPDATE_PAGE_BACKUP => [
                'controller' => UpdatePageBackupController::class,
                'method' => 'index',
            ],
            Routes::UPDATE_STEP_BACKUP => [
                'controller' => UpdatePageBackupController::class,
                'method' => 'step',
            ],
            /* step: update */
            Routes::UPDATE_PAGE_UPDATE => [
                'controller' => UpdatePageUpdateController::class,
                'method' => 'index',
            ],
            Routes::UPDATE_STEP_UPDATE => [
                'controller' => UpdatePageUpdateController::class,
                'method' => 'step',
            ],
            /* step: post update */
            Routes::UPDATE_PAGE_POST_UPDATE => [
                'controller' => UpdatePagePostUpdateController::class,
                'method' => 'index',
            ],
            Routes::UPDATE_STEP_POST_UPDATE => [
                'controller' => UpdatePagePostUpdateController::class,
                'method' => 'step',
            ],
            /* RESTORE PAGE */
            /* step: backup selection */
            Routes::RESTORE_PAGE_BACKUP_SELECTION => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'index',
            ],
            Routes::RESTORE_STEP_BACKUP_SELECTION => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'step',
            ],
            Routes::RESTORE_STEP_BACKUP_SELECTION_SAVE_FORM => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'save',
            ],
            Routes::RESTORE_STEP_BACKUP_SELECTION_SUBMIT_RESTORE_FORM => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'submitRestore',
            ],
            Routes::RESTORE_STEP_BACKUP_SELECTION_CONFIRM_RESTORE_FORM => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'startRestore',
            ],
            Routes::RESTORE_STEP_BACKUP_SELECTION_SUBMIT_DELETE_FORM => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'submitDelete',
            ],
            Routes::RESTORE_STEP_BACKUP_SELECTION_CONFIRM_DELETE_FORM => [
                'controller' => RestorePageBackupSelectionController::class,
                'method' => 'confirmDelete',
            ],
            /* step: restore */
            Routes::RESTORE_PAGE_RESTORE => [
                'controller' => RestorePageRestoreController::class,
                'method' => 'index',
            ],
            Routes::RESTORE_STEP_RESTORE => [
                'controller' => RestorePageRestoreController::class,
                'method' => 'step',
            ],
            /* step: post restore */
            Routes::RESTORE_PAGE_POST_RESTORE => [
                'controller' => RestorePagePostRestoreController::class,
                'method' => 'index',
            ],
            Routes::RESTORE_STEP_POST_RESTORE => [
                'controller' => RestorePagePostRestoreController::class,
                'method' => 'step',
            ],
            /* COMMON */
            /* error reporting */
            Routes::DISPLAY_ERROR_REPORT_MODAL => [
                'controller' => ErrorReportController::class,
                'method' => 'displayErrorReportModal',
            ],
            /* logs */
            Routes::DOWNLOAD_LOGS => [
                'controller' => LogsController::class,
                'method' => 'getDownloadLogsButton',
            ],

            Routes::ERROR_404 => [
                'controller' => Error404Controller::class,
                'method' => 'index',
            ],
        ];
}
