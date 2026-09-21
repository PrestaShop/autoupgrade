<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipper;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipperContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\ZipAction;

class ModuleUnzipperTest extends TestCase
{
    /** @var string */
    private $modulesFolder;

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->modulesFolder = sys_get_temp_dir() . '/ModuleUnzipperTest_modules_' . uniqid();
        mkdir($this->modulesFolder, 0755, true);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->removeDirectory($this->modulesFolder);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createModuleUnzipper(): ModuleUnzipper
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('trans')->willReturnArgument(0);

        $zipAction = $this->createMock(ZipAction::class);

        return new ModuleUnzipper($translator, $zipAction, $this->modulesFolder);
    }

    public function testUnzipModuleFromDirectoryCopiesModuleIntoItsOwnSubfolder()
    {
        // Simulate a pre-unzipped module directory at e.g. /tmp/.../ps_wololo
        $sourceModuleDir = sys_get_temp_dir() . '/ModuleUnzipperTest_source_' . uniqid() . '/ps_wololo';
        mkdir($sourceModuleDir, 0755, true);
        file_put_contents($sourceModuleDir . '/ps_wololo.php', '<?php // ps_wololo main file');
        file_put_contents($sourceModuleDir . '/config.xml', '<module/>');

        try {
            $context = new ModuleUnzipperContext($sourceModuleDir, 'ps_wololo');
            $unzipper = $this->createModuleUnzipper();
            $unzipper->unzipModule($context);

            // The module files must end up inside modules/ps_wololo/, NOT directly in modules/
            $this->assertDirectoryExists(
                $this->modulesFolder . '/ps_wololo',
                'Module directory modules/ps_wololo/ should have been created'
            );
            $this->assertFileExists(
                $this->modulesFolder . '/ps_wololo/ps_wololo.php',
                'Module main file should be at modules/ps_wololo/ps_wololo.php'
            );
            $this->assertFileExists(
                $this->modulesFolder . '/ps_wololo/config.xml',
                'Module config file should be at modules/ps_wololo/config.xml'
            );

            // The files must NOT have been dumped directly into modules/
            $this->assertFileNotExists(
                $this->modulesFolder . '/ps_wololo.php',
                'Module main file must NOT be placed directly in modules/'
            );
        } finally {
            $this->removeDirectory(dirname($sourceModuleDir));
        }
    }

    /**
     * mirror() copies a file only when the source is strictly newer, because Filesystem::copy()
     * compares modification times. An archive that preserves its contents' timestamps therefore
     * leaves the installed file in place and the module keeps running its previous code while
     * reporting the new version.
     */
    public function testUnzipModuleReplacesAnInstalledFileWhoseTimestampIsNotOlder()
    {
        $sourceModuleDir = sys_get_temp_dir() . '/ModuleUnzipperTest_source_' . uniqid() . '/ps_wololo';
        mkdir($sourceModuleDir, 0755, true);
        file_put_contents($sourceModuleDir . '/ps_wololo.php', '<?php // version 2');

        // Already installed, and its timestamp is NOT older than the incoming file's.
        $installedDir = $this->modulesFolder . '/ps_wololo';
        mkdir($installedDir, 0755, true);
        file_put_contents($installedDir . '/ps_wololo.php', '<?php // version 1');
        touch($sourceModuleDir . '/ps_wololo.php', time() - 60);
        touch($installedDir . '/ps_wololo.php', time());
        clearstatcache();

        try {
            $unzipper = $this->createModuleUnzipper();
            $unzipper->unzipModule(new ModuleUnzipperContext($sourceModuleDir, 'ps_wololo'));

            $this->assertSame(
                '<?php // version 2',
                file_get_contents($installedDir . '/ps_wololo.php'),
                'the incoming module file must replace the installed one whatever its timestamp'
            );
        } finally {
            $this->removeDirectory(dirname($sourceModuleDir));
        }
    }

    /**
     * The control for the test above. Removing destination files absent from the source would fix
     * the stale-file half of the issue, but it also deletes whatever a merchant keeps inside the
     * module directory - so nothing may be deleted here.
     */
    public function testUnzipModuleDoesNotDeleteFilesAbsentFromTheIncomingModule()
    {
        $sourceModuleDir = sys_get_temp_dir() . '/ModuleUnzipperTest_source_' . uniqid() . '/ps_wololo';
        mkdir($sourceModuleDir, 0755, true);
        file_put_contents($sourceModuleDir . '/ps_wololo.php', '<?php // version 2');

        $installedDir = $this->modulesFolder . '/ps_wololo';
        mkdir($installedDir . '/views', 0755, true);
        file_put_contents($installedDir . '/ps_wololo.php', '<?php // version 1');
        file_put_contents($installedDir . '/views/merchant-upload.jpg', 'merchant data');

        try {
            $unzipper = $this->createModuleUnzipper();
            $unzipper->unzipModule(new ModuleUnzipperContext($sourceModuleDir, 'ps_wololo'));

            $this->assertFileExists(
                $installedDir . '/views/merchant-upload.jpg',
                'a file the merchant put inside the module directory must survive an update'
            );
        } finally {
            $this->removeDirectory(dirname($sourceModuleDir));
        }
    }
}
