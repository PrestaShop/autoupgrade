<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class CoreUpgrader81 extends CoreUpgrader80
{
    /**
     * @throws ProcessException
     */
    public function writeNewSettings(): void
    {
        $parametersPath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/app/config/parameters.php';
        $parameters = require $parametersPath;
        if (!isset($parameters['parameters']['api_public_key']) || isset($parameters['parameters']['api_private_key'])) {
            $this->logger->debug($this->container->getTranslator()->trans('API keys not present in parameters, generating'));
            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            $this->logger->debug($this->container->getTranslator()->trans('Keys generated using openssl_pkey_new, exporting private and public keys'));
            openssl_pkey_export($privateKey, $apiPrivateKey);
            $apiPublicKey = openssl_pkey_get_details($privateKey)['key'];
            $parameters['parameters']['api_public_key'] = $apiPublicKey;
            $parameters['parameters']['api_private_key'] = $apiPrivateKey;

            $parametersContent = sprintf('<?php return %s;', var_export($parameters, true));
            $this->logger->debug($this->container->getTranslator()->trans('Updating parameters file'));
            if (!file_put_contents($parametersPath, $parametersContent)) {
                throw new ProcessException($this->container->getTranslator()->trans('Unable to migrate parameters'));
            }

            if (function_exists('opcache_invalidate')) {
                $this->logger->debug($this->container->getTranslator()->trans('Invalidating opcache for parameters file'));
                opcache_invalidate($parametersPath);
            }
            $this->logger->debug($this->container->getTranslator()->trans('Parameters file updated'));
        }
    }
}
