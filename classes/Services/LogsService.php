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

namespace PrestaShop\Module\AutoUpgrade\Services;

use Exception;
use PrestaShop\Module\AutoUpgrade\State\LogsState;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\Events;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class LogsService
{
    /** @var LogsState */
    private $state;

    /** @var Translator */
    private $translator;

    /** @var string */
    private $logsPath;

    public function __construct(LogsState $state, Translator $translator, string $logsPath)
    {
        $this->state = $state;
        $this->translator = $translator;
        $this->logsPath = $logsPath;
    }

    /**
     * @param TaskType::TASK_TYPE_* $task
     *
     * @return array{'button_label': string, 'download_path': string, 'filename': string}
     */
    public function getDownloadLogsData(string $task): array
    {
        $logsPath = $this->getDownloadLogsPath($task);

        return [
            'button_label' => $this->getDownloadLogsLabel($task),
            'button_tracking_event' => $this->getDownloadLogsTrackingEvent($task),
            'download_path' => $logsPath,
            'filename' => basename($logsPath),
        ];
    }

    /**
     * @throws Exception
     *
     * @param TaskType::TASK_TYPE_* $task
     */
    public function getLogsPath(string $task): ?string
    {
        switch ($task) {
            case TaskType::TASK_TYPE_BACKUP:
                $fileName = $this->state->getActiveBackupLogFile();
                break;
            case TaskType::TASK_TYPE_RESTORE:
                $fileName = $this->state->getActiveRestoreLogFile();
                break;
            case TaskType::TASK_TYPE_UPDATE:
                $fileName = $this->state->getActiveUpdateLogFile();
                break;
            default:
                $fileName = null;
        }

        if (!$fileName) {
            return null;
        }

        return $this->logsPath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @throws Exception
     *
     * @param TaskType::TASK_TYPE_* $task
     */
    public function getDownloadLogsPath(string $task): ?string
    {
        $logPath = $this->getLogsPath($task);
        $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

        return str_replace($documentRoot, '', $logPath);
    }

    /**
     * @param TaskType::TASK_TYPE_* $taskType
     */
    private function getDownloadLogsLabel(string $taskType): string
    {
        switch ($taskType) {
            case TaskType::TASK_TYPE_BACKUP:
                return $this->translator->trans('Download backup logs');
            case TaskType::TASK_TYPE_RESTORE:
                return $this->translator->trans('Download restore logs');
            case TaskType::TASK_TYPE_UPDATE:
                return $this->translator->trans('Download update logs');
        }
    }

    /**
     * @param TaskType::TASK_TYPE_* $taskType
     */
    private function getDownloadLogsTrackingEvent(string $taskType): string
    {
        switch ($taskType) {
            case TaskType::TASK_TYPE_BACKUP:
                return Events::BACKUP_LOGS_DOWNLOADED;
            case TaskType::TASK_TYPE_RESTORE:
                return Events::RESTORE_LOGS_DOWNLOADED;
            case TaskType::TASK_TYPE_UPDATE:
                return Events::UPDATE_LOGS_DOWNLOADED;
        }
    }
}
