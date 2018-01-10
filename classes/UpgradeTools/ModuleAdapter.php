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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Db;

class ModuleAdapter
{
    private $db;

    // Cached instance
    private $moduleDataUpdater = null;

    public function __construct($db, $upgradeVersion = null)
    {
        $this->db = $db;
    }

    /**
     * Available only from 1.7. Can't be called on PS 1.6
     * 
     * @global AppKernel $kernel
     * @return PrestaShop\PrestaShop\Adapter\Module\ModuleDataUpdater
     */
    public function getModuleDataUpdater()
    {
        global $kernel;

        if (null === $this->moduleDataUpdater) {

            if (is_null($kernel)) {
                require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
                $kernel = new AppKernel(_PS_MODE_DEV_?'dev':'prod', _PS_MODE_DEV_);
                $kernel->loadClassCache();
                $kernel->boot();
            }

            $this->moduleDataUpdater = $kernel->getContainer()->get('prestashop.core.module.updater');
        }

        return $this->moduleDataUpdater;
    }

    /**
     * Upgrade action, disabling all modules not made by PrestaShop
     *
     * Available only from 1.7. Can't be called on PS 1.6
     * `use` statements for namespaces are not used, as they can throw errors where the class does not exist
     */
    public function disableNonNativeModules()
    {
        $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
        $moduleRepository = $moduleManagerBuilder->buildRepository();
        $moduleRepository->clearCache();

        $filters = new \PrestaShop\PrestaShop\Core\Addon\AddonListFilter;
        $filters->setType(\PrestaShop\PrestaShop\Core\Addon\AddonListFilterType::MODULE)
            ->removeStatus(\PrestaShop\PrestaShop\Core\Addon\AddonListFilterStatus::UNINSTALLED);

        $installedProducts = $moduleRepository->getFilteredList($filters);

        $modules = array();
        foreach ($installedProducts as $installedProduct) {
            if (!(
                    $installedProduct->attributes->has('origin_filter_value')
                    && in_array(
                        $installedProduct->attributes->get('origin_filter_value'),
                        array(
                            \PrestaShop\PrestaShop\Core\Addon\AddonListFilterOrigin::ADDONS_NATIVE,
                            \PrestaShop\PrestaShop\Core\Addon\AddonListFilterOrigin::ADDONS_NATIVE_ALL,
                        )
                    )
                    && 'PrestaShop' === $installedProduct->attributes->get('author')
                )
                && 'autoupgrade' !== $installedProduct->attributes->get('name')) {
                $modules[] = $installedProduct->database->get('id');
            }
        }

        if (!empty($modules)) {
            $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'module` SET `active` = 0 WHERE `id_module` IN (' . implode(',', $modules) . ')');
            $this->db->execute('DELETE FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` IN (' . implode(',', $modules) . ')');
        }
    }
}