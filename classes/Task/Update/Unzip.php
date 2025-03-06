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

namespace PrestaShop\Module\AutoUpgrade\Task\Update;

use Exception;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * extract chosen version into $this->upgradeClass->latestPath directory.
 */
class Unzip extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->container->initPrestaShopCore();
    }

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $filepath = $this->container->getArchiveFilePath();
        $destExtract = $this->container->getProperty(UpgradeContainer::TMP_FILES_PATH);

        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        if ($this->container->getFilesystemAdapter()->clearDirectory($destExtract)) {
            $this->logger->debug($this->translator->trans('Temporary files directory has been emptied'));
        }
        $relative_extract_path = str_replace(_PS_ROOT_DIR_, '', $destExtract);
        $report = '';
        if (!\ConfigurationTest::test_dir($relative_extract_path, false, $report)) {
            $this->logger->error($this->translator->trans('Extraction directory %s is not writable.', [$destExtract]));
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();

            return ExitCode::FAIL;
        }

        $res = $this->container->getZipAction()->extract($filepath, $destExtract);

        if (!$res) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->info($this->translator->trans(
                'Unable to extract %filepath% file into %destination% folder...',
                [
                    '%filepath%' => $filepath,
                    '%destination%' => $destExtract,
                ]
            ));

            return ExitCode::FAIL;
        }

        // From PrestaShop 1.7, we zip all the files in another package
        // which must be unzipped too
        $newZip = $destExtract . DIRECTORY_SEPARATOR . 'prestashop.zip';
        if ($this->container->getFileSystem()->exists($newZip)) {
            $this->container->getFileSystem()->remove([
                $destExtract . DIRECTORY_SEPARATOR . '/index.php',
                $destExtract . DIRECTORY_SEPARATOR . '/Install_PrestaShop.html',
            ]);
            $subRes = $this->container->getZipAction()->extract($newZip, $destExtract);
            if (!$subRes) {
                $this->next = TaskName::TASK_ERROR;
                $this->logger->info($this->translator->trans(
                    'Unable to extract %filepath% file into %destination% folder...',
                    [
                        '%filepath%' => $filepath,
                        '%destination%' => $destExtract,
                    ]
                ));

                return ExitCode::FAIL;
            }
        } else {
            $zipSubfolder = $destExtract . '/prestashop/';
            if (!is_dir($zipSubfolder)) {
                $this->next = TaskName::TASK_ERROR;
                $this->logger->error(
                    $this->translator->trans('No prestashop/ folder found in the ZIP file. Aborting.'));

                return ExitCode::FAIL;
            }
            // /!\ On PS 1.6, files are unzipped in a subfolder PrestaShop
            foreach (scandir($zipSubfolder) as $file) {
                if ($file[0] === '.') {
                    continue;
                }
                $this->container->getFileSystem()->rename($zipSubfolder . $file, $destExtract . '/' . $file);
            }
        }

        $this->next = TaskName::TASK_UPDATE_FILES;
        $this->logger->info($this->translator->trans('File extraction complete. Now updating files...'));

        $this->container->getFileSystem()->remove($newZip);

        return ExitCode::SUCCESS;
    }
}
