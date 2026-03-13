<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\PrestashopConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use Symfony\Component\Filesystem\Filesystem;

class PrestaShopConfigurationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }
    }

    public function testPrestaShopVersionInFile()
    {
        $moduleAdapter = $this->getMockBuilder(ModuleAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $class = new PrestashopConfiguration(new Filesystem(), $moduleAdapter, __DIR__);
        $content = "<?php
define('_DB_SERVER_', '127.0.0.1:3306');
define('_DB_NAME_', 'prestashop');
define('_DB_USER_', 'root');
define('_DB_PASSWD_', 'admin');
define('_DB_PREFIX_', 'ps_');
define('_MYSQL_ENGINE_', 'InnoDB');
define('_PS_CACHING_SYSTEM_', 'CacheMemcache');
define('_PS_CACHE_ENABLED_', '0');
define('_COOKIE_KEY_', 'hgfdsq');
define('_COOKIE_IV_', 'mAJLfCuY');
define('_PS_CREATION_DATE_', '2018-03-16');
if (!defined('_PS_VERSION_'))
	define('_PS_VERSION_', '1.6.1.18');
define('_RIJNDAEL_KEY_', 'dfv');
define('_RIJNDAEL_IV_', 'fdfd==');";

        $this->assertSame('1.6.1.18', $class->findPrestaShopVersionInFile($content));
    }

    /**
     * From PrestaShop 1.7.5.0, the version is stored in the class AppKernel
     */
    public function testPrestaShopVersionInAppKernel()
    {
        $moduleAdapter = $this->getMockBuilder(ModuleAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $class = new PrestashopConfiguration(new Filesystem(), $moduleAdapter, __DIR__);
        $this->assertSame(
            '1.7.6.0',
            $class->findPrestaShopVersionInFile(
                file_get_contents(__DIR__ . '/../../fixtures/AppKernelExample.php.txt')
            )
        );
    }
}
