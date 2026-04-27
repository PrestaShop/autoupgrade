<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

class UpdateLogExists extends AbstractMiddleware
{
    public function process(): ?string
    {
        $activeUpdateLogPath = $this->upgradeContainer->getLogsService()->getLogsPath(TaskType::TASK_TYPE_UPDATE);

        if ($activeUpdateLogPath === null
            || !$this->upgradeContainer->getFileSystem()->exists($activeUpdateLogPath)) {
            return Routes::HOME_PAGE;
        }

        return null;
    }
}
