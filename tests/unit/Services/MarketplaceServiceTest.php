<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Services;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\MarketplaceApiException;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\Module;
use PrestaShop\Module\AutoUpgrade\Services\MarketplaceService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class TestableMarketplaceService extends MarketplaceService
{
    /** @var array<string, string|false> */
    public $stubResponses = [];

    /** @var int */
    public $fetchCallCount = 0;

    /**
     * @param string[] $toFetch
     *
     * @return array<string, string|false>
     */
    protected function fetchModulesRaw(array $toFetch): array
    {
        ++$this->fetchCallCount;

        return array_intersect_key($this->stubResponses, array_flip($toFetch));
    }
}

class MarketplaceServiceTest extends TestCase
{
    /** @var TestableMarketplaceService */
    private $service;

    protected function setUp(): void
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('trans')->willReturnArgument(0);

        $this->service = new TestableMarketplaceService($translator);
    }

    private function validModuleJson(): string
    {
        return json_encode([
            'attributes' => [],
            'compliance' => [],
            'product' => [],
            'technical_info' => [],
        ]);
    }

    public function testEmptyInputReturnsEmptyArray(): void
    {
        $result = $this->service->getModuleDetails([]);

        $this->assertSame([], $result);
    }

    public function testSingleModuleValidJsonReturnsModule(): void
    {
        $this->service->stubResponses = ['foo' => $this->validModuleJson()];

        $result = $this->service->getModuleDetails(['foo']);

        $this->assertArrayHasKey('foo', $result);
        $this->assertInstanceOf(Module::class, $result['foo']);
    }

    public function testSingleModuleFalseResponseReturnsApiNotCallableException(): void
    {
        $this->service->stubResponses = ['foo' => false];

        $result = $this->service->getModuleDetails(['foo']);

        $this->assertArrayHasKey('foo', $result);
        $this->assertInstanceOf(MarketplaceApiException::class, $result['foo']);
        $this->assertSame(MarketplaceApiException::API_NOT_CALLABLE_CODE, $result['foo']->getCode());
    }

    public function testSingleModuleInvalidJsonReturnsEmptyDataException(): void
    {
        $this->service->stubResponses = ['foo' => 'not-json'];

        $result = $this->service->getModuleDetails(['foo']);

        $this->assertArrayHasKey('foo', $result);
        $this->assertInstanceOf(MarketplaceApiException::class, $result['foo']);
        $this->assertSame(MarketplaceApiException::EMPTY_DATA_CODE, $result['foo']->getCode());
    }

    public function testMultipleModulesAllReturnedCorrectly(): void
    {
        $this->service->stubResponses = [
            'foo' => $this->validModuleJson(),
            'bar' => false,
            'baz' => 'bad-json',
        ];

        $result = $this->service->getModuleDetails(['foo', 'bar', 'baz']);

        $this->assertCount(3, $result);
        $this->assertInstanceOf(Module::class, $result['foo']);
        $this->assertInstanceOf(MarketplaceApiException::class, $result['bar']);
        $this->assertSame(MarketplaceApiException::API_NOT_CALLABLE_CODE, $result['bar']->getCode());
        $this->assertInstanceOf(MarketplaceApiException::class, $result['baz']);
        $this->assertSame(MarketplaceApiException::EMPTY_DATA_CODE, $result['baz']->getCode());
    }

    public function testSameNameCalledTwiceDoesNotRefetch(): void
    {
        $this->service->stubResponses = ['foo' => $this->validModuleJson()];

        $this->service->getModuleDetails(['foo']);
        $this->service->getModuleDetails(['foo']);

        $this->assertSame(1, $this->service->fetchCallCount);
    }

    public function testGetModuleDetailDelegatesToGetModuleDetailsAndReturnsModule(): void
    {
        $this->service->stubResponses = ['foo' => $this->validModuleJson()];

        $result = $this->service->getModuleDetail('foo');

        $this->assertInstanceOf(Module::class, $result);
    }

    public function testGetModuleDetailThrowsWhenMapValueIsException(): void
    {
        $this->service->stubResponses = ['foo' => false];

        $this->expectException(MarketplaceApiException::class);
        $this->service->getModuleDetail('foo');
    }
}
