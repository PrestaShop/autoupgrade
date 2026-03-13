<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

class RestoreLogExists extends AbstractMiddleware
{
    public function process(): ?string
    {
        $activeRestoreLogPath = $this->upgradeContainer->getLogsService()->getLogsPath(TaskType::TASK_TYPE_RESTORE);

        if ($activeRestoreLogPath === null
            || !$this->upgradeContainer->getFileSystem()->exists($activeRestoreLogPath)) {
            return Routes::HOME_PAGE;
        }

        return null;
    }
}
