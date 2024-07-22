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

class ModuleMigration
{
    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $module_name;

    /** @var string | null */
    private $db_version;

    /** @var string | null */
    private $local_version;

    /** @var string[] */
    private $upgrade_files;

    /** @var string */
    private $upgrade_files_root_path;

    /** @var \Module | bool */
    private $module_instance;

    public function __construct(Translator $translator, Logger $logger, string $module_name)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->module_name = $module_name;
        $this->db_version = null;
        $this->local_version = null;
        $this->upgrade_files = [];
        $this->upgrade_files_root_path = _PS_MODULE_DIR_ . $module_name . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws UpgradeException
     */
    public function initUpgrade(): void
    {
        $this->module_instance = \Module::getInstanceByName($this->module_name);

        if (!$this->module_instance) {
            throw (new UpgradeException($this->translator->trans('[WARNING] Error when trying to retrieve module %s instance.', [$this->module_name])))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }

        $this->local_version = $this->module_instance->version;

        $this->db_version = ModuleVersionAdapter::get($this->module_name) ?? '0';

        if ($this->db_version === '0') {
            $this->logger->notice($this->translator->trans('No version found in database for module %s, all files for upgrade will be applied.', [$this->module_name]));
        }
    }

    public function needUpgrade(): bool
    {
        if (version_compare($this->local_version, $this->db_version, '>')) {
            if (empty($this->upgrade_files)) {
                $this->loadUpgradeFiles();
            }
            return !empty($this->upgrade_files);
        }
        return false;
    }

    public function loadUpgradeFiles(): void
    {
        if (!empty($this->upgrade_files)) {
            $this->upgrade_files = [];
        }

        $files = glob($this->upgrade_files_root_path . '/*.php', GLOB_BRACE);

        foreach ($files as $file) {
            if (preg_match('/(?:upgrade|install)[_-](\d+(?:\.\d+){0,2}).php$/', basename($file), $matches)) {
                $fileVersion = $matches[1];
                if (version_compare($fileVersion, $this->db_version, '>') && version_compare($fileVersion, $this->local_version, '<=')) {
                    $this->upgrade_files[] = ['file' => $file, 'version' => $fileVersion];
                    $this->logger->notice($this->translator->trans('File %s will be added to upgrade file list.', [$file]));
                }
            }
        }

        usort($this->upgrade_files, function($a, $b) {
            return version_compare($a['version'], $b['version']);
        });

        $this->upgrade_files = array_column($this->upgrade_files, 'file');
    }
}
