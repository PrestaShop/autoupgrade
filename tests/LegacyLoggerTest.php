<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;

class LegacyLoggerTest extends TestCase
{
    public function testLastInfoIsRegistered()
    {
        $logger = new LegacyLogger();
        $logger->log(LegacyLogger::INFO, 'Hello');

        $this->assertSame('Hello', $logger->getLastInfo());
    }

    public function testSeveralLastInfoAreRegistered()
    {
        $logger = new LegacyLogger();
        $logger->log(LegacyLogger::INFO, 'Hello');
        $logger->log(LegacyLogger::INFO, 'Good bye');

        $this->assertSame('Good bye', $logger->getLastInfo());
        $infos = $logger->getInfos();
        $this->assertSame('Hello', end($infos));
        $this->assertCount(1, $infos);
    }

    public function testErrorIsRegistered()
    {
        $logger = new LegacyLogger();
        $logger->log(LegacyLogger::CRITICAL, 'Ach!!!');

        $errors = $logger->getErrors();
        $this->assertCount(1, $errors);
        $this->assertCount(0, $logger->getInfos());
        $this->assertSame('Ach!!!', end($errors));
    }

    public function testMessageIsRegistered()
    {
        $logger = new LegacyLogger();
        $logger->log(LegacyLogger::DEBUG, 'Some stuff happened');

        $messages = $logger->getInfos();
        $this->assertCount(1, $messages);
        $this->assertCount(0, $logger->getErrors());
        $this->assertSame('Some stuff happened', end($messages));
    }
}
