<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class BackupChoiceHasBeenMade extends AbstractMiddleware
{
    public function process(): ?string
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();

        if ($updateConfiguration->isBackupCompleted() === null) {
            return Routes::UPDATE_PAGE_BACKUP_OPTIONS;
        }

        return null;
    }
}
