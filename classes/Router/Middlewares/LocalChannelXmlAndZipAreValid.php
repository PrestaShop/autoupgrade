<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class LocalChannelXmlAndZipAreValid extends AbstractMiddleware
{
    public function process(): ?string
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();

        if ($updateConfiguration->isChannelLocal()) {
            $errors = $this->upgradeContainer->getLocalChannelConfigurationValidator()->validate($updateConfiguration->toArray());

            if (!empty($errors)) {
                return Routes::UPDATE_PAGE_VERSION_CHOICE;
            }
        }

        return null;
    }
}
