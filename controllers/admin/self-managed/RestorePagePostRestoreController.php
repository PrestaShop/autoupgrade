<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\RestoreSteps;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\VersionUtils;

class RestorePagePostRestoreController extends AbstractPageWithStepController
{
    const CURRENT_STEP = RestoreSteps::STEP_POST_RESTORE;

    protected function getPageTemplate(): string
    {
        return 'restore';
    }

    protected function getStepTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::RESTORE_PAGE_POST_RESTORE;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_RESTORE);
        $currentPsVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);
        $currentMajorVersion = VersionUtils::splitPrestaShopVersion($currentPsVersion)['major'];

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'exit_link' => $this->upgradeContainer->getUrlGenerator()->getShopAdminAbsolutePathFromRequest($this->request),
                'dev_doc_link' => DocumentationLinks::getDevDocUpdateAssistantPostRestoreUrl($currentMajorVersion),
                'download_logs' => $this->upgradeContainer->getLogsService()->getDownloadLogsData(TaskType::TASK_TYPE_RESTORE),
            ]
        );
    }
}
