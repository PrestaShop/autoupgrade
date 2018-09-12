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
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;

class UpgradeConfigurationTest extends TestCase
{
    /**
     * This method only initialize the configuration from empty data saves.
     * We expect to find all the default data.
     */
    public function testDefaultValuesAreSet()
    {
        $filePath = sys_get_temp_dir();
        $fileName = __FUNCTION__ . '.dat';

        $upgradeConfigurationStorage = new UpgradeConfigurationStorage($filePath . DIRECTORY_SEPARATOR);
        $upgradeConfiguration = $upgradeConfigurationStorage->load($fileName);

        foreach ($upgradeConfigurationStorage->getDefaultData() as $key => $value) {
            $this->assertSame($value, $upgradeConfiguration->get($key));
        }
    }

    /**
     * In case the data save contains some values, we still expect to find the dault data
     * to be defined, even if they can't be found in the saved file.
     */
    public function testDefaultValuesAreSetWhenNotExistingInSavedFile()
    {
        $filePath = sys_get_temp_dir();
        $fileName = __FUNCTION__ . '.dat';

        (new FileConfigurationStorage($filePath . DIRECTORY_SEPARATOR))->save(['randomData' => 'trololo'], $fileName);

        $upgradeConfigurationStorage = new UpgradeConfigurationStorage($filePath . DIRECTORY_SEPARATOR);
        $upgradeConfiguration = $upgradeConfigurationStorage->load($fileName);

        foreach ($upgradeConfigurationStorage->getDefaultData() as $key => $value) {
            $this->assertSame($value, $upgradeConfiguration->get($key));
        }
    }
}
