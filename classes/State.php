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

namespace PrestaShop\Module\AutoUpgrade;

/**
 * Class storing the temporary data to keep between 2 ajax requests
 */
class State
{
    private $install_version; // Destination version of PrestaShop
    private $backupName = null;
    private $backupFilesFilename = null;
    private $backupDbFilename = null;
    private $restoreName = null;
    private $restoreFilesFilename = null;
    private $restoreDbFilenames = array();
    /**
     * installedLanguagesIso is an array of iso_code of each installed languages
     * @var array
     */
    private $installedLanguagesIso = array();
    /**
     * modules_addons is an array of array(id_addons => name_module).
     * @var array
     */
    private $modules_addons = array();

    // ToDo: To be moved in ajax response ?
    private $warning_exists = false;

    /**
     * @param array $savedState from another request
     */
    public function importFromArray(array $savedState)
    {
        foreach($savedState as $name => $value) {
            if (!empty($value) && property_exists(__CLASS__, $name)) {
                $this->{$name} = $value;
            }
        }
        return $this;
    }

    /**
     * @return array of class properties for export
     */
    public function export()
    {
        return get_object_vars($this);
    }

    /*
     * GETTERS
     */
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

    public function getWarningExists()
    {
        return $this->warning_exists;
    }

    /*
     * SETTERS
     */
    public function setInstallVersion($install_version)
    {
        $this->install_version = $install_version;
        return $this;
    }

    public function setBackupName($backupName)
    {
        $this->backupName = $backupName;
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

    public function setWarningExists($warning_exists)
    {
        $this->warning_exists = $warning_exists;
        return $this;
    }


}