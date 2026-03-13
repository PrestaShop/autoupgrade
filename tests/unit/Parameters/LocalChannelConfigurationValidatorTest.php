<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
