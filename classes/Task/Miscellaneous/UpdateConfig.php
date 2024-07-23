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

namespace PrestaShop\Module\AutoUpgrade\Task\Miscellaneous;

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * update configuration after validating the new values.
 */
class UpdateConfig extends AbstractTask
{
    /**
     * Data being passed by CLI entry point
     *
     * @var array<string, mixed>
     */
    protected $cliParameters;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        // nothing next
        $this->next = '';

        // Was coming from AdminSelfUpgrade::currentParams before
        $configurationData = $this->getConfigurationData();
        $config = [];
        // update channel
        if (isset($configurationData['channel'])) {
            $config['channel'] = $configurationData['channel'];
            $config['archive.filename'] = Upgrader::DEFAULT_FILENAME;
            // Switch on default theme if major upgrade (i.e: 1.6 -> 1.7)
            $config['PS_AUTOUP_CHANGE_DEFAULT_THEME'] = ($configurationData['channel'] === 'major');
        }
        if (isset($configurationData['private_release_link'], $configurationData['private_release_md5'])) {
            $config['channel'] = 'private';
            $config['private_release_link'] = $configurationData['private_release_link'];
            $config['private_release_md5'] = $configurationData['private_release_md5'];
            $config['private_allow_major'] = $configurationData['private_allow_major'];
        }

        if (!empty($configurationData['archive_prestashop'])) {
            $file = $configurationData['archive_prestashop'];
            $fullFilePath = $this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($fullFilePath)) {
                $this->setErrorFlag();
                $this->logger->info($this->translator->trans('File %s does not exist. Unable to select that channel.', [$file]));

                return ExitCode::FAIL;
            }

            try {
                $targetVersion = $this->extractPrestashopVersionFromZip($fullFilePath);
            } catch (Exception $exception) {
                $this->setErrorFlag();
                $this->logger->info($this->translator->trans('Unable to retrieve version from zip: %s.', [$exception->getMessage()]));

                return ExitCode::FAIL;
            }

            $xmlFile = $configurationData['archive_xml'];
            $fullXmlPath = $this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $xmlFile;
            if (!empty($xmlFile) && !file_exists($fullXmlPath)) {
                $this->setErrorFlag();
                $this->logger->info($this->translator->trans('File %s does not exist. Unable to select that channel.', [$xmlFile]));

                return ExitCode::FAIL;
            }

            $xmlVersion = $this->getXmlVersion($fullXmlPath);

            if ($xmlVersion !== $targetVersion) {
                $this->error = true;
                $this->logger->info($this->translator->trans('Prestashop version detected in the xml (%s) does not match the zip version (%s).', [$xmlVersion, $targetVersion]));

                return ExitCode::FAIL;
            }

            $config['channel'] = 'archive';
            $config['archive.filename'] = $configurationData['archive_prestashop'];
            $config['archive.version_num'] = $targetVersion;
            $config['archive.xml'] = $configurationData['archive_xml'];

            $this->logger->info($this->translator->trans('Upgrade process will use archive.'));
        }
        if (isset($configurationData['directory_num'])) {
            $config['channel'] = 'directory';
            if (empty($configurationData['directory_num']) || strpos($configurationData['directory_num'], '.') === false) {
                $this->setErrorFlag();
                $this->logger->info($this->translator->trans('Version number is missing. Unable to select that channel.'));

                return ExitCode::FAIL;
            }

            $config['directory.version_num'] = $configurationData['directory_num'];
        }

        foreach (UpgradeConfiguration::UPGRADE_CONST_KEYS as $key) {
            if (!isset($configurationData[$key])) {
                continue;
            }
            // The PS_DISABLE_OVERRIDES variable must only be updated on the database side
            if ($key === 'PS_DISABLE_OVERRIDES') {
                UpgradeConfiguration::updatePSDisableOverrides((bool) $configurationData[$key]);
            } else {
                $config[$key] = $configurationData[$key];
            }
        }

        if (!$this->writeConfig($config)) {
            $this->setErrorFlag();
            $this->logger->info($this->translator->trans('Error on saving configuration'));

            return ExitCode::FAIL;
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function inputCliParameters($parameters): void
    {
        $this->cliParameters = $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigurationData(): array
    {
        if (null !== $this->cliParameters) {
            return $this->getCLIParams();
        }

        return $this->getRequestParams();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCLIParams(): array
    {
        if (empty($this->cliParameters)) {
            throw new \RuntimeException('Empty CLI parameters - did CLI entry point failed to provide data?');
        }

        return $this->cliParameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequestParams(): array
    {
        return empty($_REQUEST['params']) ? [] : $_REQUEST['params'];
    }

    /**
     * update module configuration (saved in file UpgradeFiles::configFilename) with $new_config.
     *
     * @param array<string, mixed> $config
     *
     * @return bool true if success
     *
     * @throws Exception
     */
    private function writeConfig(array $config): bool
    {
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::CONFIG_FILENAME) && !empty($config['channel'])) {
            $this->container->getUpgrader()->channel = $config['channel'];
            $this->container->getUpgrader()->checkPSVersion();

            $this->container->getState()->setInstallVersion($this->container->getUpgrader()->version_num);
            $this->container->getState()->setOriginVersion($this->container->getProperty(UpgradeContainer::PS_VERSION));
        }

        $this->container->getUpgradeConfiguration()->merge($config);
        $this->logger->info($this->translator->trans('Configuration successfully updated.') . ' <strong>' . $this->translator->trans('This page will now be reloaded and the module will check if a new version is available.') . '</strong>');

        return (new UpgradeConfigurationStorage($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR))->save($this->container->getUpgradeConfiguration(), UpgradeFileNames::CONFIG_FILENAME);
    }

    /**
     * @throws ZipActionException
     * @throws Exception
     */
    private function extractPrestashopVersionFromZip(string $zipFile): string
    {
        $internalZipFileName = 'prestashop.zip';
        $versionFile = 'install/install_version.php';

        if (!file_exists($zipFile)) {
            throw new FileNotFoundException("Unable to find $zipFile file");
        }
        $zip = $this->container->getZipAction()->open($zipFile);
        $internalZipContent = $this->container->getZipAction()->extractFileFromArchive($zip, $internalZipFileName);
        $zip->close();

        $tempInternalZipPath = $this->createTemporaryFile($internalZipContent);

        $internalZip = $this->container->getZipAction()->open($tempInternalZipPath);
        $fileContent = $this->container->getZipAction()->extractFileFromArchive($internalZip, $versionFile);
        $internalZip->close();

        @unlink($tempInternalZipPath);

        return $this->extractVersionFromContent($fileContent);
    }

    /**
     * @throws IOException
     */
    private function createTemporaryFile(string $content): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'internal_zip_');
        if (file_put_contents($tempFilePath, $content) === false) {
            throw new IOException('Unable to create temporary file');
        }

        return $tempFilePath;
    }

    /**
     * @throws RuntimeException
     */
    private function extractVersionFromContent(string $content): string
    {
        $pattern = "/define\\('_PS_INSTALL_VERSION_', '([\\d.]+)'\\);/";
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        } else {
            throw new RuntimeException('Unable to extract version from content');
        }
    }

    private function getXmlVersion(string $xmlPath): string
    {
        $xml = @simplexml_load_file($xmlPath);

        return $xml->ps_root_dir['version'];
    }
}
