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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Marketplace\Client as MarketplaceClient;

class MarketplaceClientTest extends TestCase
{
    /**
     * Makes an actual call to the marketplace API
     */
    public function testDefaultClient()
    {
        $marketplaceClient = new MarketplaceClient();
        $modules = $marketplaceClient->getNativesModules('1.7.5.0');

        $this->assertTrue(!empty($modules));
    }

    public function testWhenApiReturn500()
    {
        $mock = new MockHandler([
            new Response(500),
        ]);
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $marketplaceClient = new MarketplaceClient();
        $marketplaceClient->setClient($client);

        $this->expectException(ServerException::class);
        $marketplaceClient->getNativesModules('1.7.5.0');
    }

    public function testWhenAPIReturnNothing()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $marketplaceClient = new MarketplaceClient();
        $marketplaceClient->setClient($client);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The answer from the Marketplace API is empty.');
        $marketplaceClient->getNativesModules('1.7.5.0');
    }

    public function testWhenAPIReturnAnExplicitError()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['errors' => '💀'])),
        ]);
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $marketplaceClient = new MarketplaceClient();
        $marketplaceClient->setClient($client);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error returned from the Marketplace: \'💀\'');
        $marketplaceClient->getNativesModules('1.7.5.0');
    }

    public function testWhenAPIReturnAnExplicitErrorWithLabel()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['errors' => ['code' => 1, 'label' => '🔥']])),
        ]);
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $marketplaceClient = new MarketplaceClient();
        $marketplaceClient->setClient($client);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error returned from the Marketplace: 🔥');
        $marketplaceClient->getNativesModules('1.7.5.0');
    }

    public function testWhenAPIReturnSomethingButModules()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['something_else_than_modules' => ['a', 'b', 'c']])),
        ]);
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $marketplaceClient = new MarketplaceClient();
        $marketplaceClient->setClient($client);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There was no modules in the content returned by the marketplace.');
        $marketplaceClient->getNativesModules('1.7.5.0');
    }
}