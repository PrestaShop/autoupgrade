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
            ->setConstructorArgs([__DIR__, __DIR__ . '/..'])
            ->setMethods(['getDb'])
            ->getMock();
        $actualClass = get_class(call_user_func([$container, $functionName]));
        $this->assertSame($actualClass, $expectedClass);
    }

    public function objectsToInstanciateProvider()
    {
        // | Function to call | Expected class |
        return [
            ['getCacheCleaner', PrestaShop\Module\AutoUpgrade\UpgradeTools\CacheCleaner::class],
            ['getChecksumCompare', PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare::class],
            ['getCookie', PrestaShop\Module\AutoUpgrade\Cookie::class],
            ['getFileConfigurationStorage', PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage::class],
            ['getFileFilter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter::class],
            // ['getUpgrader', \PrestaShop\Module\AutoUpgrade\Upgrader::class],
            ['getFilesystemAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter::class],
            ['getFileLoader', PrestaShop\Module\AutoUpgrade\Xml\FileLoader::class],
            ['getLogger', PrestaShop\Module\AutoUpgrade\Log\LegacyLogger::class],
            ['getModuleAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\ModuleAdapter::class],
            ['getState', \PrestaShop\Module\AutoUpgrade\State::class],
            ['getSymfonyAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter::class],
            ['getTranslationAdapter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation::class],
            ['getTranslator', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator::class],
            // Cannot be run in the context of unit tests as the class would be loaded from PS dependencies
            // ['getTwig', \Twig\Environment::class],
            ['getPrestaShopConfiguration', PrestaShop\Module\AutoUpgrade\PrestashopConfiguration::class],
            ['getUpgradeConfiguration', PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration::class],
            ['getWorkspace', PrestaShop\Module\AutoUpgrade\Workspace::class],
            ['getZipAction', PrestaShop\Module\AutoUpgrade\ZipAction::class],
        ];
    }
}
