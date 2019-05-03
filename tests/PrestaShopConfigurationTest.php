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
use PrestaShop\Module\AutoUpgrade\PrestashopConfiguration;

class PrestaShopConfigurationTest extends TestCase
{
    public function testPrestaShopVersionInFile()
    {
        $class = new PrestashopConfiguration(__DIR__, __DIR__);
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
        $class = new PrestashopConfiguration(__DIR__, __DIR__);
        $this->assertSame(
            '1.7.6.0',
            $class->findPrestaShopVersionInFile(
                file_get_contents(__DIR__ . '/fixtures/AppKernelExample.php.txt')
            )
        );
    }
}
