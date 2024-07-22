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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Module;

class ModuleMigration
{
    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $module_name;

    /** @var string | null */
    private $dbVersion;

    /** @var string | null */
    private $localVersion;

    /** @var string[] */
    private $upgradeFiles;

    /** @var string */
    private $upgradeFilesRootPath;

    public function __construct(Translator $translator, Logger $logger, string $module_name)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->module_name = $module_name;
        $this->dbVersion = null;
        $this->localVersion = null;
        $this->upgradeFiles = [];
        $this->upgradeFilesRootPath = _PS_MODULE_DIR_ . $module_name . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws UpgradeException
     */
    public function initUpgrade() {
        $moduleInstance = Module::getInstanceByName($this->module_name);

        if (!$moduleInstance) {
            throw (new UpgradeException($this->translator->trans('[WARNING] Error when trying to retrieve module %s instance.', [$this->module_name])))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }

        $this->localVersion = $this->moduleInstance->version;

        $this->dbVersion = ModuleVersionAdapter::get($this->module_name) ?? '0';

        if ($this->dbVersion) {
            $this->logger->notice($this->translator->trans('No version found in database for module %s, all files for upgrade will be applied.', [$this->module_name]));
        }
    }

    public function needUpgrade(): boolean {
        if (version_compare($this->localVersion, $this->dbVersion, '>')) {
            if (empty($this->upgradeFiles)) {
                $this->loadUpgradeFiles();
            }
            return !empty($this->upgradeFiles);
        }
        return false;
    }

    public function loadUpgradeFiles() {
        if (!empty($this->upgradeFiles)) {
            $this->upgradeFiles = [];
        }

        $files = glob($this->upgradeFilesRootPath . '/*.php', GLOB_BRACE);

        foreach ($files as $file) {
            if (preg_match('/(?:upgrade|install)[_-](\d+(?:\.\d+){0,2}).php$/', basename($file), $matches)) {
                $fileVersion = $matches[1];
                if (version_compare($fileVersion, $this->dbVersion, '>') && version_compare($fileVersion, $this->localVersion, '<=')) {
                    $this->upgradeFiles[] = ['file' => $file, 'version' => $fileVersion];
                    $this->logger->notice($this->translator->trans('File %s will be added to upgrade file list', [$file]));
                }
            }
        }

        usort($this->upgradeFiles, function($a, $b) {
            return version_compare($a['version'], $b['version']);
        });

        $this->upgradeFiles = array_column($this->upgradeFiles, 'file');
    }
}
