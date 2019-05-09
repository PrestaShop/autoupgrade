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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class UpgradeContainerTest extends TestCase
{
    public function testSameResultFormAdminSubDir()
    {
        $container = new UpgradeContainer(__DIR__, __DIR__ . '/..');
        $this->assertNotSame($container->getProperty(UpgradeContainer::PS_ADMIN_SUBDIR), str_replace($container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $container->getProperty(UpgradeContainer::PS_ADMIN_PATH)));
    }

    /**
     * @dataProvider objectsToInstanciateProvider
     */
    public function testObjectInstanciation($functionName, $expectedClass)
    {
        $container = $this->getMockBuilder(UpgradeContainer::class)
            ->setConstructorArgs(array(__DIR__, __DIR__ . '/..'))
            ->setMethods(array('getDb'))
            ->getMock();
        $actualClass = get_class(call_user_func(array($container, $functionName)));
        $this->assertSame($actualClass, $expectedClass);
    }

    public function objectsToInstanciateProvider()
    {
        // | Function to call | Expected class |
        return array(
            array('getCacheCleaner', PrestaShop\Module\AutoUpgrade\UpgradeTools\CacheCleaner::class),
            array('getCookie', PrestaShop\Module\AutoUpgrade\Cookie::class),
            array('getFileConfigurationStorage', PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage::class),
            array('getFileFilter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter::class),
            // array('getUpgrader', \PrestaShop\Module\AutoUpgrade\Upgrader::class),
            array('getFilesystemAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter::class),
            array('getLogger', PrestaShop\Module\AutoUpgrade\Log\LegacyLogger::class),
            array('getModuleAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\ModuleAdapter::class),
            array('getState', \PrestaShop\Module\AutoUpgrade\State::class),
            array('getSymfonyAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter::class),
            array('getTranslationAdapter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation::class),
            array('getTranslator', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator::class),
            array('getTwig', Twig_Environment::class),
            array('getPrestaShopConfiguration', PrestaShop\Module\AutoUpgrade\PrestashopConfiguration::class),
            array('getUpgradeConfiguration', PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration::class),
            array('getWorkspace', PrestaShop\Module\AutoUpgrade\Workspace::class),
            array('getZipAction', PrestaShop\Module\AutoUpgrade\ZipAction::class),
        );
    }
}
