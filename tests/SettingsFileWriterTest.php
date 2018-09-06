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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SettingsFileWriter;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class SettingsFileWriterTest extends TestCase
{
    private $container;
    private $settingsWriter;

    protected function setUp()
    {
        parent::setUp();
        $this->container = new UpgradeContainer('/html', '/html/admin');
        $this->settingsWriter = new SettingsFileWriter($this->container->getTranslator());
    }

    public function testSettingsIsProperlyGenerated()
    {
        $fileExpected = "<?php
define('_DB_SERVER_', '127.0.0.1:3307');
define('_DB_NAME_', 'prestashop16');
define('_DB_USER_', 'root');
define('_DB_PREFIX_', 'ps_');
define('_MYSQL_ENGINE_', 'InnoDB');
define('_PS_CACHING_SYSTEM_', 'CacheMemcache');
define('_PS_CACHE_ENABLED_', '0');
define('_COOKIE_KEY_', 'dedeedededede');
define('_COOKIE_IV_', 'wololo');
define('_PS_CREATION_DATE_', '2018-05-16');
define('_PS_VERSION_', '1.6.1.18');
define('_RIJNDAEL_KEY_', 'zrL1GDp2oqDoXFss');
define('_RIJNDAEL_IV_', 'QSt/I95YtA==');
";

        $datas = array(
            '_DB_SERVER_' => '127.0.0.1:3307',
            '_DB_NAME_' => 'prestashop16',
            '_DB_USER_' => 'root',
            '_DB_PREFIX_' => 'ps_',
            '_MYSQL_ENGINE_' => 'InnoDB',
            '_PS_CACHING_SYSTEM_' => 'CacheMemcache',
            '_PS_CACHE_ENABLED_' => '0',
            '_COOKIE_KEY_' => 'dedeedededede',
            '_COOKIE_IV_' => 'wololo',
            '_PS_CREATION_DATE_' => '2018-05-16',
            '_PS_VERSION_' => '1.6.1.18',
            '_RIJNDAEL_KEY_' => 'zrL1GDp2oqDoXFss',
            '_RIJNDAEL_IV_' => 'QSt/I95YtA==',
        );

        $file = tempnam(sys_get_temp_dir(), 'PSS');
        $this->settingsWriter->writeSettingsFile($file, $datas);

        $this->assertSame($fileExpected, file_get_contents($file));
    }
}
