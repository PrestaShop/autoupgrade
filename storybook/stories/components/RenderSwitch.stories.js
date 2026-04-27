/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import RenderSwitch from "../../../views/templates/components/render-switch.html.twig";

export default {
  title: "Components/Render fields",
  component: RenderSwitch,
};

export const Switch = {
  args: {
    id: "disable_non_native_modules",
    name: "disable_non_native_modules",
    title: "Deactivate non-native modules",
    description:
      "All the modules installed after creating your store are considered non-native modules. They might be incompatible with the new version of PrestaShop. We recommend deactivating them during the update.",
    value: true,
    required: false,
  },
};
