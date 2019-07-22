<?php
/*
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2019 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Client;

use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;

class ModuleDetailsClient
{
    const API_ENDPOINT = 'https://api.addons.prestashop.com/?method=search&query=5496&search_type=full&version=%s';
    /**
     * Module details front the API
     *
     * @var object
     */
    protected $moduleDetails;

    /**
     * PrestaShop version to use when requesting the marketplace API
     *
     * @var string
     */
    protected $psVersion;

    /**
     * @var CircuitBreakerInterface
     */
    protected $circuitBreaker;

    public function __construct($psVersion)
    {
        $this->psVersion = $psVersion;

        $circuitBreakerFactory = new SimpleCircuitBreakerFactory();
        $this->circuitBreaker = $circuitBreakerFactory->create(new FactorySettings(2, 0.1, 10));
    }

    /**
     * Retrieve and return the module details from the API
     *
     * @return object
     */
    public function getDetails()
    {
        if (null === $this->moduleDetails) {
            $this->requestDetails();
        }

        return $this->moduleDetails;
    }

    /**
     * Module version available on the Marketplace
     *
     * @var string
     */
    public function getVersion()
    {
        return $this->getDetails()->version;
    }

    /**
     * Requests the API and loads the data in memory
     */
    public function requestDetails()
    {
        $response = $this->call();
        if (!isset($response->results[0])) {
            throw new \Exception('Module details are missing from response.');
        }
        $this->moduleDetails = $response->results[0];
    }

    public function call()
    {
        $fallbackResponse = function () {
            return '{"results":[{"version":"0.0.0.0"}]}';
        };

        return json_decode(
            $this->circuitBreaker->call(
                sprintf(self::API_ENDPOINT, $this->psVersion),
                [],
                $fallbackResponse
            )
        );
    }
}
