<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;

class RestoreIsConfigured extends AbstractMiddleware
{
    public function process(): ?string
    {
        return $this->upgradeContainer->getFileStorage()->exists(UpgradeFileNames::RESTORE_CONFIG_FILENAME) ? null : Routes::RESTORE_PAGE_BACKUP_SELECTION;
    }
}
