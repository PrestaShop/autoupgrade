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
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Log\StreamedLogger;

class StreamedLoggerTest extends TestCase
{
    /**
     * @dataProvider filtersProvider
     */
    public function testFiltersProperlyApplied($level, $filterLevel, $expected)
    {
        $logger = new StreamedLogger();
        $logger->setFilter($filterLevel);
        $this->assertSame($expected, $logger->isFiltered($level));
    }

    public function filtersProvider()
    {
        return array(
            array(Logger::EMERGENCY, Logger::INFO, false),
            array(Logger::INFO, Logger::EMERGENCY, true),
            array(Logger::ERROR, Logger::ERROR, false),
            array(Logger::ERROR, Logger::WARNING, false),
            array(Logger::ERROR, Logger::CRITICAL, true),
        );
    }
}
