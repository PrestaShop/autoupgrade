<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Module;

/**
 * Interface AddonsClientInterface is used to perform a request on the Addons API.
 */
interface AddonsClientInterface
{
    /**
     * Returns a list of modules from addons.
     *
     * The type parameters specify which type of modules you need:
     *  - native (developed by PrestaShop)
     *  - service (for 1.6 version only)
     *  - must-have
     *  - module (you will need an id_module additional parameter)
     *
     * The params array allows you to specify additional parameters:
     *  - version (PrestaShop version)
     *  - iso_code (Which country you target, by default all)
     *  - format (by default xml)
     *
     * @param string $request
     * @param array $params
     *
     * @return array|false List of modules name indexed by their Addons id
     */
    public function request($type, array $params);
}
