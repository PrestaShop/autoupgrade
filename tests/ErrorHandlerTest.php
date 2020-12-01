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
        // The exception will be sent to the stdout,
        // we enable the output buffering
        ob_start();
        $this->errorHandler->exceptionHandler($exception);
        ob_end_clean();

        $errors = $this->logger->getErrors();
        $this->assertCount(1, $errors);
        $this->assertContains('[INTERNAL] ' . __FILE__ . ' line ' . $line . ' - Exception: ERMAGHERD', end($errors));
    }

    public function testWarningInErrorHandler()
    {
        $line = __LINE__;
        $this->errorHandler->errorHandler(E_WARNING, 'Trololo', __FILE__, $line);
        $msgs = $this->logger->getInfos();
        $this->assertCount(0, $this->logger->getErrors());
        $this->assertCount(1, $msgs);
        $this->assertSame(end($msgs), '[INTERNAL] ' . __FILE__ . ' line ' . $line . ' - Trololo');
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
