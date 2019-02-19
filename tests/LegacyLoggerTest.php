<?php
/*
 * 2007-2018 PrestaShop
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
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
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
