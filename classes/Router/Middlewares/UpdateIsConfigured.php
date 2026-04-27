<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;

class UpdateIsConfigured extends AbstractMiddleware
{
    public function process(): ?string
    {
        return $this->upgradeContainer->getFileStorage()->exists(UpgradeFileNames::UPDATE_CONFIG_FILENAME) ? null : Routes::UPDATE_PAGE_VERSION_CHOICE;
    }
}
