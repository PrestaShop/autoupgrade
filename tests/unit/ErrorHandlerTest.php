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
use PrestaShop\Module\AutoUpgrade\ErrorHandler;
use PrestaShop\Module\AutoUpgrade\Log\WebLogger;

class ErrorHandlerTest extends TestCase
{
    protected $errorHandler;
    protected $adminSelfUpgradeStub;
    protected $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->logger = new WebLogger();
        $this->errorHandler = $this->getMockBuilder(ErrorHandler::class)
            ->setConstructorArgs([$this->logger])
            ->setMethods(['terminate'])
            ->getMock();
    }

    public function testDefaultContentIsEmpty()
    {
        $this->assertEmpty($this->logger->getLogs());
    }

    public function testCheckExceptionAndContent()
    {
        $exception = new Exception('ERMAGHERD');
        $line = __LINE__ - 1;
        // The exception will be sent to the stdout,
        // we enable the output buffering
        ob_start();
        $this->errorHandler->exceptionHandler($exception);
        ob_end_clean();

        $infos = $this->logger->getLogs();
        $this->assertCount(1, $infos);
        $this->assertContains(__FILE__ . ' line ' . $line . ' - Exception: ERMAGHERD', end($infos));
    }

    public function testWarningInErrorHandler()
    {
        $line = __LINE__;
        $this->errorHandler->errorHandler(E_WARNING, 'Trololo', __FILE__, $line);
        $msgs = $this->logger->getLogs();
        $this->assertCount(1, $msgs);
        $this->assertSame(end($msgs), 'WARNING - ' . __FILE__ . ' line ' . $line . ' - Trololo');
    }

    public function testAdminDirIsEscaped()
    {
        $this->logger->setSensitiveData(['my_admin' => '**admin_folder**']);

        ob_start();
        $this->errorHandler->exceptionHandler(new Exception('Open /store/my_admin/wololo.php'));
        ob_get_clean();

        $infos = $this->logger->getLogs();
        $this->assertCount(1, $infos);
        $this->assertNotContains('my_admin', end($infos));
    }

    /**
     * @dataProvider logProvider
     */
    public function testGeneratedJsonLog($log, $type)
    {
        $this->assertNotNull(json_decode($this->errorHandler->generateJsonLog($log, $type)));
    }

    public function logProvider()
    {
        return [
            ["/var/www/html/modules/autoupgrade/classes/Task/Upgrade/BackupFiles.php line 55 - Class 'PrestaShop\Module\AutoUpgrade\Task\Upgrade\UpgradeContainer' not found", 'WARNING'],
            ["/var/www/html/modules/autoupgrade/classes/Task/Upgrade/BackupDb.php line 105 - Can't use method return value in write context", 'ALERT'],
        ];
    }
}
