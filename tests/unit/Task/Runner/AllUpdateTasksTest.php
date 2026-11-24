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

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllUpdateTasks;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class AllUpdateTasksTest extends TestCase
{
    /** @var array<string, mixed> */
    private $originalServer;

    /** @var string[] */
    private $loggedMessages;

    protected function setUp()
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
        $this->loggedMessages = [];
    }

    protected function tearDown()
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    public function testRestartCommandIsLoggedWithTheOriginalArguments()
    {
        $_SERVER['argv'] = ['bin/console', 'update:start', '--action=UpdateFiles', '--config-file-path=config.json', '--chain'];

        $this->assertTrue($this->checkIfRestartRequested(TaskName::TASK_UPDATE_DATABASE));
        $this->assertContains('$ bin/console update:start --chain --action=UpdateDatabase', $this->loggedMessages);
    }

    /**
     * The update command can be run from a context that does not populate $_SERVER['argv'],
     * for instance a PHP script calling the console application over HTTP.
     * The restart must still be requested instead of stopping the update with a fatal error.
     */
    public function testRestartIsRequestedWhenArgvIsNotAvailable()
    {
        unset($_SERVER['argv']);

        $this->assertTrue($this->checkIfRestartRequested(TaskName::TASK_UPDATE_DATABASE));
        $this->assertContains('$  --action=UpdateDatabase', $this->loggedMessages);
    }

    public function testRestartIsNotRequestedForATaskThatDoesNotNeedIt()
    {
        unset($_SERVER['argv']);

        $this->assertFalse($this->checkIfRestartRequested(TaskName::TASK_UPDATE_COMPLETE));
        $this->assertEmpty($this->loggedMessages);
    }

    private function checkIfRestartRequested(string $nextTask): bool
    {
        $logger = $this->createMock(Logger::class);
        $logger->method('info')->willReturnCallback(function (string $message) {
            $this->loggedMessages[] = $message;
        });

        $container = new UpgradeContainer(__DIR__, __DIR__ . '/..');
        $container->setLogger($logger);
        $runner = new AllUpdateTasks($container);

        $response = $this->getMockBuilder(AjaxResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $response->method('getStepDone')->willReturn(true);
        $response->method('getNext')->willReturn($nextTask);

        $method = new ReflectionMethod(AllUpdateTasks::class, 'checkIfRestartRequested');
        $method->setAccessible(true);

        return $method->invoke($runner, $response);
    }
}
