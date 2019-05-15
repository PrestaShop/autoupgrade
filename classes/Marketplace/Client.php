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

namespace PrestaShop\Module\AutoUpgrade\Marketplace;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
 * This class is responsible of calling the Marketplace API
 * 
 * Duplicated from the core for PS 1.6
 */
class Client
{
    const MARKETPLACE_URL = 'https://api.addons.prestashop.com/';

    /**
     * @var GuzzleClient
     */
    private $addonsApiClient;

    private $queryParameters;

    private $defaultQueryParameters = [
        'action' => 'native',
        'iso_code' => 'all',
        'method' => 'listing',
        'format' => 'json',
    ];

    public function __construct()
    {
        $this->addonsApiClient = new GuzzleClient();
        $this->queryParameters = $this->defaultQueryParameters;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(GuzzleClient $client)
    {
        $this->addonsApiClient = $client;

        return $this;
    }

    /**
     * In case you reuse the Client, you may want to clean the previous parameters.
     */
    public function reset()
    {
        $this->queryParameters = $this->defaultQueryParameters;
    }

    /**
     * Retrive all the native modules for a given PrestaShop version
     * 
     * @param string $version PrestaShop version
     * @return array List of native modules
     */
    public function getNativesModules($version)
    {
        $response = $this->setVersion($version)
            ->getResponse();

        if (empty($response)) {
            throw new Exception('The answer from the Marketplace API is empty.');
        }

        $responseArray = json_decode($response, true);

        if (empty($responseArray)) {
            throw new Exception(sprintf('Could not decode the answer from the Marketplace, Looks like the answer is not a valid JSON (%s)', $response));
        }

        if (isset($responseArray['errors'])) {
            if (isset($responseArray['errors']['label'])) {
                throw new Exception(sprintf('Error returned from the Marketplace: %s', $responseArray['errors']['label']));
            }
            throw new Exception(sprintf('Error returned from the Marketplace: %s', var_export($responseArray['errors'], true)));
        }

        if (!isset($responseArray['modules'])) {
            throw new Exception('There was no modules in the content returned by the marketplace.');
        }
        return $responseArray['modules'];
    }

    /**
     * Sends the configured request then return its body
     * 
     * @return string
     */
    public function getResponse()
    {
        return (string) $this->addonsApiClient
            ->get(self::MARKETPLACE_URL, ['query' => $this->queryParameters])
            ->getBody();
    }

    /**
     * Setter for PrestaShop version
     * 
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->queryParameters['version'] = $version;

        return $this;
    }
}
