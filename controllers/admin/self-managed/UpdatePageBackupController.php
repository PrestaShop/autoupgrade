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

class UpdatePageBackupController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_BACKUP;

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        return $this->redirectTo(Routes::UPDATE_PAGE_BACKUP_OPTIONS);
    }

    protected function getPageTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function getStepTemplate(): string
    {
        // Different from self::CURRENT_STEP, because a refresh should return to the backup options.
        return 'backup';
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::UPDATE_PAGE_BACKUP;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_UPDATE);

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'success_route' => Routes::UPDATE_STEP_BACKUP_OPTIONS,
                'submit_skip_backup_route' => Routes::UPDATE_STEP_BACKUP_SUBMIT_UPDATE,
                'download_logs_route' => Routes::DOWNLOAD_LOGS,
                'download_logs_type' => TaskType::TASK_TYPE_BACKUP,
                'retry_route' => Routes::UPDATE_PAGE_BACKUP_OPTIONS,
                'submit_error_report_route' => Routes::DISPLAY_ERROR_REPORT_MODAL,
                'initial_process_action' => TaskName::TASK_BACKUP_INITIALIZATION,
                'download_logs_parent_id' => PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            ]
        );
    }
}
