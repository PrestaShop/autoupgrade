<?php
/*
 * 2007-2019 PrestaShop
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
 *  @copyright  2007-2019 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Marketplace;

class ModuleList
{
    /**
     * @var MarketplaceClientInterface
     */
    private $marketplaceClient;

    public function __construct(MarketplaceClientInterface $client = null)
    {
        if (null === $client) {
            $client = new Client();
        }
        $this->marketplaceClient = $client;
    }

    public function compareNativeModuleLists($originalVersion, $destinationVersion)
    {

        $nativeModulesOnOriginalVersion = $this->marketplaceClient->getNativesModules($originalVersion);
        $nativeModulesOnDestinationVersion = $this->marketplaceClient->getNativesModules($destinationVersion);

        $modules = [
            'new' => [],
            'common' => [],
            'deleted' => [],
        ];

        foreach ($nativeModulesOnDestinationVersion as $dModule) {
            // Only native modules should be managed automatically.
            // All other ones must be only listed.
            if ('PrestaShop' !== $dModule['author']) {
                $modules['common'][] = $dModule;
                continue;
            }

            foreach ($nativeModulesOnOriginalVersion as $oKey => &$oModule) {
                if ($oModule['name'] !== $dModule['name']) {
                    continue;
                }

                // If we find the module if both lists, it's considered as common
                $modules['common'][] = $dModule;
                unset($nativeModulesOnOriginalVersion[$oKey]);
                continue 2;
            }
            // If we did not find it in the original list, we consider as New
            $modules['new'][] = $dModule;
        }
        // All modules not deleted in the list of the original PS version may be considered as deleted
        $modules['deleted'] += $nativeModulesOnOriginalVersion;

        return $modules;
    }
}
