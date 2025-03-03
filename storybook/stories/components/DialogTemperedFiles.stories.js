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

import DialogTemperedFiles from "../../../views/templates/dialogs/dialog-tempered-files.html.twig";
import { Default as Dialog } from "./Dialog.stories";

export default {
  title: "Components/Dialog",
  component: DialogTemperedFiles,
};

export const TemperedFiles = {
  args: {
    ...Dialog.args,
    title: "List of core alterations",
    message:
      "Some core files have been altered, customization made on these files will be lost during the update.",
    missing_files: [
      "adminProjetX/autoupgrade/index.php",
      "adminProjetX/backups/index.php",
      "config/xml/.htaccess",
      "config/xml/themes/index.php",
    ],
    altered_files: [
      "adminProjetX/themes/new-theme/public/tax.bundle.js",
      "adminProjetX/themes/new-theme/public/order_return_states_form.bundle.js",
      "adminProjetX/themes/new-theme/public/carrier.bundle.js",
      "adminProjetX/themes/new-theme/public/create_product_default_theme.css",
      "adminProjetX/themes/new-theme/public/meta.bundle.js",
      "adminProjetX/themes/new-theme/public/module.bundle.js",
      "adminProjetX/themes/new-theme/public/tax.bundle.js",
      "adminProjetX/themes/new-theme/public/order_return_states_form.bundle.js",
      "adminProjetX/themes/new-theme/public/carrier.bundle.js",
      "adminProjetX/themes/new-theme/public/create_product_default_theme.css",
      "adminProjetX/themes/new-theme/public/meta.bundle.js",
      "adminProjetX/themes/new-theme/public/module.bundle.js",
    ],
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
