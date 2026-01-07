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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * PrestaShop provides a seamless integration of modules by loading them automatically when they are installed.
 * The autloader is called, the services added in the container...
 *
 * However while we update the core, we need to limit the loaded classes to the strict minimum.
 * Adding all the modules is a risk as they may be incompatible with the destination version until we try updating them.
 *
 * We fake the list of installed modules by altering the links between the list of installed modules and the modules folders.
 */
class QuarantineZone
{
    const DISABLED_BY_SAFE_MODE = 'UA_';

    /**
     * Enable the safe mode of PrestaShop by preventing the execution of modules
     */
    public function addAll(): void
    {
        DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . 'module` m
            SET `name` = CONCAT("' . self::DISABLED_BY_SAFE_MODE . '", `name`)'
        );
    }

    /**
     * Disabling the safe mode of PrestaShop by restoring modules state
     */
    public function removeAll(): void
    {
        DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . 'module` m
            SET `name` = REPLACE(`name`, "' . self::DISABLED_BY_SAFE_MODE . '", "")'
        );
    }

    public function removeOne(string $moduleName): void
    {
        DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . 'module` m
            SET `name` = REPLACE(`name`, "' . self::DISABLED_BY_SAFE_MODE . '", "")
            WHERE `name` LIKE "%' . pSQL($moduleName) . '"'
        );
    }
}
