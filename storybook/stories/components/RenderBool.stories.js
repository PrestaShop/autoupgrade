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

import RenderBool from "../../../views/templates/components/render-bool.html.twig";

export default {
  title: "Components/Render fields",
  component: RenderBool,
  argTypes: {
    type: {
      control: 'select',
      options: ['disabled', 'bool', 'radio', 'select', 'textarea', 'container', 'container_end', 'text'],
      defaultValue: 'bool',
    },
  },
  args: {
    field: {
      id: "deactivate_modules",
      title: "Deactivate non-native modules",
      desc: "All the modules installed after creating your store are considered non-native modules. They might be incompatible with the new version of PrestaShop. We recommend deactivating them during the update.",
      js: {
        on: 'onclick="enableFeature()"',
        off: 'onclick="disableFeature()"',
      },
      type: 'bool',
      required: true,
      disabled: true,
    },
    key: "PS_AUTOUP_CUSTOM_MOD_DESACT",
    val: true,
  },
};

export const Boolean = {};
