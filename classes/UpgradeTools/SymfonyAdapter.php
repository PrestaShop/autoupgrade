<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

/**
 * TODO: Create a class for 1.7 env and another one for 1.6 ?
 */
class SymfonyAdapter
{
    /**
     * @var string Version on which PrestaShop is being upgraded
     */
    private $destinationPsVersion;

    public function __construct($destinationPsVersion)
    {
        $this->destinationPsVersion = $destinationPsVersion;
    }

    public function runSchemaUpgradeCommand()
    {
        if (version_compare($this->destinationPsVersion, '1.7.1.1', '>=')) {
            $schemaUpgrade = new \PrestaShopBundle\Service\Database\Upgrade();
            $outputCommand = 'prestashop:schema:update-without-foreign';
        } else {
            $schemaUpgrade = new \PrestaShopBundle\Service\Cache\Refresh();
            $outputCommand = 'doctrine:schema:update';
        }

        $schemaUpgrade->addDoctrineSchemaUpdate();
        $output = $schemaUpgrade->execute();

        return $output[$outputCommand];
    }

    /**
     * Return the AppKernel, after initialization
     *
     * @return \AppKernel
     */
    public function initAppKernel()
    {
        global $kernel;
        if (!$kernel instanceof \AppKernel) {
            require_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
            $env = (true == _PS_MODE_DEV_) ? 'dev' : 'prod';
            $kernel = new \AppKernel($env, _PS_MODE_DEV_);
            $kernel->loadClassCache();
            $kernel->boot();
        }

        return $kernel;
    }
}
