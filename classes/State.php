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

namespace PrestaShop\Module\AutoUpgrade;

/**
 * Class storing the temporary data to keep between 2 ajax requests.
 */
class State
{
    private $install_version; // Destination version of PrestaShop
    private $backupName;
    private $backupFilesFilename;
    private $backupDbFilename;
    private $restoreName;
    private $restoreFilesFilename;
    private $restoreDbFilenames = [];

    // STEP BackupDb
    private $backup_lines;
    private $backup_loop_limit;
    private $backup_table;

    /**
     * Int during BackupDb, allowing the script to increent the number of different file names
     * String during step RestoreDb, which contains the file to process (Data coming from toRestoreQueryList).
     *
     * @var string|int Contains the SQL progress
     */
    private $dbStep = 0;

    /**
     * Data filled in upgrade warmup, to avoid risky tasks during the process.
     *
     * @var array|null File containing sample files to be deleted
     */
    private $removeList;
    /**
     * @var string|null File containing files to be upgraded
     */
    private $fileToUpgrade;
    /**
     * @var string|null File containing modules to be upgraded
     */
    private $modulesToUpgrade;

    /**
     * installedLanguagesIso is an array of iso_code of each installed languages.
     *
     * @var array
     */
    private $installedLanguagesIso = [];
    /**
     * modules_addons is an array of array(id_addons => name_module).
     *
     * @var array
     */
    private $modules_addons = [];
    /**
     * modules_versions is an array of array(id_addons => version of the module).
     *
     * @var array<string, string>
     */
    private $modules_versions = [];

    /**
     * @var bool Determining if all steps went totally successfully
     */
    private $warning_exists = false;

    /**
     * @param array $savedState from another request
     */
    public function importFromArray(array $savedState)
    {
        foreach ($savedState as $name => $value) {
            if (!empty($value) && property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $encodedData
     */
    public function importFromEncodedData($encodedData)
    {
        $decodedData = json_decode(base64_decode($encodedData), true);
        if (empty($decodedData['nextParams'])) {
            return $this;
        }

        return $this->importFromArray($decodedData['nextParams']);
    }

    /**
     * @return array of class properties for export
     */
    public function export()
    {
        return get_object_vars($this);
    }

    public function initDefault(Upgrader $upgrader, $prodRootDir, $version)
    {
        $postData = http_build_query([
            'action' => 'native',
            'iso_code' => 'all',
            'method' => 'listing',
            'version' => $this->getInstallVersion(),
        ]);
        $xml_local = $prodRootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'modules_native_addons.xml';
        $xml = $upgrader->getApiAddons($xml_local, $postData, true);

        $modules_addons = $modules_versions = [];
        if (is_object($xml)) {
            foreach ($xml as $mod) {
                $modules_addons[(string) $mod->id] = (string) $mod->name;
                $modules_versions[(string) $mod->id] = (string) $mod->version;
            }
        }
        $this->setModulesAddons($modules_addons);
        $this->setModulesVersions($modules_versions);

        // installedLanguagesIso is used to merge translations files
        $installedLanguagesIso = array_map(
            function ($v) { return $v['iso_code']; },
            \Language::getIsoIds(false)
        );
        $this->setInstalledLanguagesIso($installedLanguagesIso);

        $rand = dechex(mt_rand(0, min(0xffffffff, mt_getrandmax())));
        $date = date('Ymd-His');
        $backupName = 'V' . $version . '_' . $date . '-' . $rand;
        // Todo: To be moved in state class? We could only require the backup name here
        // I.e = $this->upgradeContainer->getState()->setBackupName($backupName);, which triggers 2 other setters internally
        $this->setBackupName($backupName);
    }

    // GETTERS
    public function getInstallVersion()
    {
        return $this->install_version;
    }

    public function getBackupName()
    {
        return $this->backupName;
    }

    public function getBackupFilesFilename()
    {
        return $this->backupFilesFilename;
    }

    public function getBackupDbFilename()
    {
        return $this->backupDbFilename;
    }

    public function getBackupLines()
    {
        return $this->backup_lines;
    }

    public function getBackupLoopLimit()
    {
        return $this->backup_loop_limit;
    }

    public function getBackupTable()
    {
        return $this->backup_table;
    }

    public function getDbStep()
    {
        return $this->dbStep;
    }

    public function getRemoveList()
    {
        return $this->removeList;
    }

    public function getRestoreName()
    {
        return $this->restoreName;
    }

    public function getRestoreFilesFilename()
    {
        return $this->restoreFilesFilename;
    }

    public function getRestoreDbFilenames()
    {
        return $this->restoreDbFilenames;
    }

    public function getInstalledLanguagesIso()
    {
        return $this->installedLanguagesIso;
    }

    public function getModules_addons()
    {
        return $this->modules_addons;
    }

    /**
     * @return array<string, string>
     */
    public function getModulesVersions()
    {
        return $this->modules_versions;
    }

    public function getWarningExists()
    {
        return $this->warning_exists;
    }

    // SETTERS
    public function setInstallVersion($install_version)
    {
        $this->install_version = $install_version;

        return $this;
    }

    public function setBackupName($backupName)
    {
        $this->backupName = $backupName;
        $this->setBackupFilesFilename('auto-backupfiles_' . $backupName . '.zip')
            ->setBackupDbFilename('auto-backupdb_XXXXXX_' . $backupName . '.sql');

        return $this;
    }

    public function setBackupFilesFilename($backupFilesFilename)
    {
        $this->backupFilesFilename = $backupFilesFilename;

        return $this;
    }

    public function setBackupDbFilename($backupDbFilename)
    {
        $this->backupDbFilename = $backupDbFilename;

        return $this;
    }

    public function setBackupLines($backup_lines)
    {
        $this->backup_lines = $backup_lines;

        return $this;
    }

    public function setBackupLoopLimit($backup_loop_limit)
    {
        $this->backup_loop_limit = $backup_loop_limit;

        return $this;
    }

    public function setBackupTable($backup_table)
    {
        $this->backup_table = $backup_table;

        return $this;
    }

    public function setDbStep($dbStep)
    {
        $this->dbStep = $dbStep;

        return $this;
    }

    public function setRemoveList($removeList)
    {
        $this->removeList = $removeList;

        return $this;
    }

    public function setRestoreName($restoreName)
    {
        $this->restoreName = $restoreName;

        return $this;
    }

    public function setRestoreFilesFilename($restoreFilesFilename)
    {
        $this->restoreFilesFilename = $restoreFilesFilename;

        return $this;
    }

    public function setRestoreDbFilenames($restoreDbFilenames)
    {
        $this->restoreDbFilenames = $restoreDbFilenames;

        return $this;
    }

    public function setInstalledLanguagesIso($installedLanguagesIso)
    {
        $this->installedLanguagesIso = $installedLanguagesIso;

        return $this;
    }

    public function setModulesAddons($modules_addons)
    {
        $this->modules_addons = $modules_addons;

        return $this;
    }

    /**
     * @param array<string, string> $modules_versions
     *
     * @return self
     */
    public function setModulesVersions($modules_versions)
    {
        $this->modules_versions = $modules_versions;

        return $this;
    }

    public function setWarningExists($warning_exists)
    {
        $this->warning_exists = $warning_exists;

        return $this;
    }
}
