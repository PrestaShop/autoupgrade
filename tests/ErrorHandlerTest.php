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
use PrestaShop\Module\AutoUpgrade\ErrorHandler;

class ErrorHandlerTest extends TestCase
{
    protected $errorHandler;
    protected $adminSelfUpgradeStub;

    protected function setUp()
    {
        parent::setUp();

        $this->adminSelfUpgradeStub = $this->getMockBuilder(AdminSelfUpgrade::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->adminSelfUpgradeStub->nextErrors = array();
        $this->errorHandler = new ErrorHandler($this->adminSelfUpgradeStub);
    }

    public function testDefaultContentIsEmpty()
    {
        $this->assertEmpty($this->adminSelfUpgradeStub->nextErrors);
    }

    public function testCheckExceptionAndContent()
    {
        $exception = new Exception('ERMAGHERD');$line = __LINE__;
        $this->errorHandler->exceptionHandler($exception);
        $this->assertEquals(1, count($this->adminSelfUpgradeStub->nextErrors));
        $this->assertEquals(end($this->adminSelfUpgradeStub->nextErrors), '[INTERNAL] '.__FILE__.' line '.$line.' - Exception: ERMAGHERD');
    }

    public function testFatalErrorHandler()
    {
        $line = __LINE__;
        $this->errorHandler->errorHandler(E_WARNING, 'Trololo', __FILE__, $line);
        $this->assertEquals(1, count($this->adminSelfUpgradeStub->nextErrors));
        $this->assertEquals(end($this->adminSelfUpgradeStub->nextErrors), '[INTERNAL] '.__FILE__.' line '.$line.' - WARNING: Trololo');
    }
}