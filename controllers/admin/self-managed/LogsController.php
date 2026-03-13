<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogsController extends AbstractGlobalController
{
    public function getDownloadLogsButton(): JsonResponse
    {
        $type = TaskType::fromString(
            $this->request->request->get('download-logs-type')
        );

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/components/download_logs.html.twig',
                $this->upgradeContainer->getLogsService()->getDownloadLogsData($type)
            )
        );
    }
}
