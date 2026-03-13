<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class HasBackupAvailable extends AbstractMiddleware
{
    public function process(): ?string
    {
        $backups = $this->upgradeContainer->getBackupFinder()->getAvailableBackups();

        if (empty($backups)) {
            return Routes::HOME_PAGE;
        }

        return null;
    }
}
