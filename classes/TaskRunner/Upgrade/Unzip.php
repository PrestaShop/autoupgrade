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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use Exception;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;

/**
 * extract chosen version into $this->upgradeClass->latestPath directory.
 */
class Unzip extends AbstractTask
{
    /**
     * @throws Exception
     */
    public function run()
    {
        $filepath = $this->container->getFilePath();
        $destExtract = $this->container->getProperty(UpgradeContainer::LATEST_PATH);

        if (file_exists($destExtract)) {
            FilesystemAdapter::deleteDirectory($destExtract, false);
            $this->logger->debug($this->translator->trans('"/latest" directory has been emptied'));
        }
        $relative_extract_path = str_replace(_PS_ROOT_DIR_, '', $destExtract);
        $report = '';
        if (!\ConfigurationTest::test_dir($relative_extract_path, false, $report)) {
            $this->logger->error($this->translator->trans('Extraction directory %s is not writable.', [$destExtract]));
            $this->next = 'error';
            $this->error = true;

            return false;
        }

        $res = $this->container->getZipAction()->extract($filepath, $destExtract);

        if (!$res) {
            $this->next = 'error';
            $this->error = true;
            $this->logger->info($this->translator->trans(
                'Unable to extract %filepath% file into %destination% folder...',
                [
                    '%filepath%' => $filepath,
                    '%destination%' => $destExtract,
                ]
            ));

            return false;
        }

        // From PrestaShop 1.7, we zip all the files in another package
        // which must be unzipped too
        $newZip = $destExtract . DIRECTORY_SEPARATOR . 'prestashop.zip';
        if (file_exists($newZip)) {
            @unlink($destExtract . DIRECTORY_SEPARATOR . '/index.php');
            @unlink($destExtract . DIRECTORY_SEPARATOR . '/Install_PrestaShop.html');

            $subRes = $this->container->getZipAction()->extract($newZip, $destExtract);
            if (!$subRes) {
                $this->next = 'error';
                $this->logger->info($this->translator->trans(
                    'Unable to extract %filepath% file into %destination% folder...',
                    [
                        '%filepath%' => $filepath,
                        '%destination%' => $destExtract,
                    ]
                ));

                return false;
            }
        } else {
            $filesystem = new Filesystem();
            $zipSubfolder = $destExtract . '/prestashop/';
            if (!is_dir($zipSubfolder)) {
                $this->next = 'error';
                $this->logger->error(
                    $this->translator->trans('No prestashop/ folder found in the ZIP file. Aborting.'));

                return;
            }
            // /!\ On PS 1.6, files are unzipped in a subfolder PrestaShop
            foreach (scandir($zipSubfolder) as $file) {
                if ($file[0] === '.') {
                    continue;
                }
                $filesystem->rename($zipSubfolder . $file, $destExtract . '/' . $file);
            }
        }

        // Unsetting to force listing
        $this->container->getState()->setRemoveList(null);
        $this->next = 'backupFiles';
        $this->logger->info($this->translator->trans('File extraction complete. Now backing up files...'));

        @unlink($newZip);

        return true;
    }
}
