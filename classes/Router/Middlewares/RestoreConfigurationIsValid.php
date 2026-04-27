<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class RestoreConfigurationIsValid extends AbstractMiddleware
{
    public function process(): ?string
    {
        $restoreConfiguration = $this->upgradeContainer->getRestoreConfiguration();

        $errors = $this->upgradeContainer->getRestoreConfigurationValidator()->validate($restoreConfiguration->toArray());

        if (!empty($errors)) {
            return Routes::RESTORE_PAGE_BACKUP_SELECTION;
        }

        return null;
    }
}
