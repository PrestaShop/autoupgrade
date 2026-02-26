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
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\Validator\LocalChannelConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class LocalChannelConfigurationValidatorTest extends TestCase
{
    /**
     * @var LocalChannelConfigurationValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->container = new UpgradeContainer('/html', '/html/admin');

        $downloadPath = __DIR__ . '/../../fixtures/localChannel/';

        $this->validator = new LocalChannelConfigurationValidator(
            $this->container->getTranslator(),
            $this->container->getPrestashopVersionService(),
            $downloadPath
        );
    }

    public function testValidateReturnsErrorIfNoConfigurationExists()
    {
        $data = [];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => "Both 'xml' and 'zip' files attributes must be provided to use the local channel.",
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfZipFileDoesNotExist()
    {
        $data = [UpdateConfiguration::ARCHIVE_ZIP => 'non_existent.zip', UpdateConfiguration::ARCHIVE_XML => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'File ' . $data[UpdateConfiguration::ARCHIVE_ZIP] . ' does not exist. Unable to select that channel.',
            'target' => UpdateConfiguration::ARCHIVE_ZIP,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfNotVersionedZipFile()
    {
        $data = [UpdateConfiguration::ARCHIVE_ZIP => 'not_versioned_8.2.0.zip', UpdateConfiguration::ARCHIVE_XML => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.',
            'target' => UpdateConfiguration::ARCHIVE_ZIP,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfXmlFileDoesNotExist()
    {
        $data = [UpdateConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpdateConfiguration::ARCHIVE_XML => 'non_existent.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'File ' . $data[UpdateConfiguration::ARCHIVE_XML] . ' does not exist. Unable to select that channel.',
            'target' => UpdateConfiguration::ARCHIVE_XML,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfNotVersionedXmlFile()
    {
        $data = [UpdateConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpdateConfiguration::ARCHIVE_XML => 'not_versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'We couldn\'t find a PrestaShop version in the XML file that was uploaded in your local archive. Please try again.',
            'target' => UpdateConfiguration::ARCHIVE_XML,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfVersionsDoNotMatch()
    {
        $data = [UpdateConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpdateConfiguration::ARCHIVE_XML => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'The PrestaShop version in your archive doesn\'t match the one in XML file. Please fix this issue and try again.',
        ], $result[0]);
    }

    public function testValidatePassesWithValidFiles()
    {
        $data = [UpdateConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpdateConfiguration::ARCHIVE_XML => 'versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertEmpty($result);
    }
}
