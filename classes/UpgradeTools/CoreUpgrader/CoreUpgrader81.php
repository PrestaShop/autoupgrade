<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeException;

class CoreUpgrader81 extends CoreUpgrader80
{
    /** @var bool */
    private $settingsMigrated = false;

    public function doUpgrade()
    {
        // We need to write the new settings before initConstants() is called
        // because the new settings are needed for the Kernel
        $this->writeNewSettings();
        $this->settingsMigrated = true;

        parent::doUpgrade();
    }

    public function writeNewSettings()
    {
        if ($this->settingsMigrated) {
            return;
        }

        $parametersPath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/app/config/parameters.php';
        $parameters = require $parametersPath;
        if (!isset($parameters['parameters']['api_public_key']) || isset($parameters['parameters']['api_private_key'])) {
            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            openssl_pkey_export($privateKey, $apiPrivateKey);
            $apiPublicKey = openssl_pkey_get_details($privateKey)['key'];
            $parameters['parameters']['api_public_key'] = $apiPublicKey;
            $parameters['parameters']['api_private_key'] = $apiPrivateKey;

            $parametersContent = sprintf('<?php return %s;', var_export($parameters, true));
            if (!file_put_contents($parametersPath, $parametersContent)) {
                throw new UpgradeException($this->container->getTranslator()->trans('Unable to migrate parameters', [], 'Modules.Autoupgrade.Admin'));
            }
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($parametersPath);
            }
            $this->logger->debug($this->container->getTranslator()->trans('Parameters file updated', [], 'Modules.Autoupgrade.Admin'));
        }
    }
}
