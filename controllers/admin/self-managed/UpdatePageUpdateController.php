<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\UpdateSteps;

class UpdatePageUpdateController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_UPDATE;

    protected function getPageTemplate(): string
    {
        return 'update';
    }

    protected function getStepTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::UPDATE_PAGE_UPDATE;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_UPDATE);
        $backupFinder = $this->upgradeContainer->getBackupFinder();

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'success_route' => Routes::UPDATE_STEP_POST_UPDATE,
                'download_logs_route' => Routes::DOWNLOAD_LOGS,
                'download_logs_type' => TaskType::TASK_TYPE_UPDATE,
                'restore_route' => Routes::RESTORE_PAGE_BACKUP_SELECTION,
                'submit_error_report_route' => Routes::DISPLAY_ERROR_REPORT_MODAL,
                'initial_process_action' => TaskName::TASK_UPDATE_INITIALIZATION,
                'backup_available' => !empty($backupFinder->getAvailableBackups()),
                'download_logs_parent_id' => PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            ]
        );
    }
}
