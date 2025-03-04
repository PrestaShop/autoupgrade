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

import RenderSwitch from "../../../views/templates/components/render-switch.html.twig";

export default {
  title: "Components/Render fields",
  component: RenderSwitch,
};

export const Switch = {
  args: {
    id: "PS_AUTOUP_CUSTOM_MOD_DESACT",
    name: "PS_AUTOUP_CUSTOM_MOD_DESACT",
    title: "Deactivate non-native modules",
    description:
      "All the modules installed after creating your store are considered non-native modules. They might be incompatible with the new version of PrestaShop. We recommend deactivating them during the update.",
    value: true,
    required: false,
  },
};
