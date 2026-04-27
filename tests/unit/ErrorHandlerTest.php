<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
