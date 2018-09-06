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
use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;

class ErrorHandlerTest extends TestCase
{
    protected $errorHandler;
    protected $adminSelfUpgradeStub;
    protected $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->logger = new LegacyLogger();
        $this->errorHandler = new ErrorHandler($this->logger);
    }

    public function testDefaultContentIsEmpty()
    {
        $this->assertEmpty($this->logger->getErrors());
    }

    public function testCheckExceptionAndContent()
    {
        $exception = new Exception('ERMAGHERD');
        $line = __LINE__ - 1;
        $this->errorHandler->exceptionHandler($exception);

        $errors = $this->logger->getErrors();
        $this->assertEquals(1, count($errors));
        $this->assertContains('[INTERNAL] ' . __FILE__ . ' line ' . $line . ' - Exception: ERMAGHERD', end($errors));
    }

    public function testWarningInErrorHandler()
    {
        $line = __LINE__;
        $this->errorHandler->errorHandler(E_WARNING, 'Trololo', __FILE__, $line);
        $msgs = $this->logger->getInfos();
        $this->assertEquals(0, count($this->logger->getErrors()));
        $this->assertEquals(1, count($msgs));
        $this->assertEquals(end($msgs), '[INTERNAL] ' . __FILE__ . ' line ' . $line . ' - Trololo');
    }

    /**
     * @dataProvider logProvider
     */
    public function testGeneratedJsonLog($log)
    {
        $this->assertNotNull(json_decode($this->errorHandler->generateJsonLog($log)));
    }

    public function logProvider()
    {
        return array(
            array("[INTERNAL] /var/www/html/modules/autoupgrade/classes/TaskRunner/Upgrade/BackupFiles.php line 55 - Class 'PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\UpgradeContainer' not found"),
            array("[INTERNAL] /var/www/html/modules/autoupgrade/classes/TaskRunner/Upgrade/BackupDb.php line 105 - Can't use method return value in write context"),
        );
    }
}
