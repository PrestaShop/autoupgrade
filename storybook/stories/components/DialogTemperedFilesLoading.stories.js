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

import DialogTamperedFiles from "../../../views/templates/dialogs/dialog-tempered-files.html.twig";
import { Default as Dialog } from "./Dialog.stories";

export default {
  title: "Components/Dialog/TamperedFiles",
  component: DialogTamperedFiles,
};

export const Loading = {
  args: {
    ...Dialog.args,
    title: "List of core alterations",
    message:
      "Some core files have been altered, customization made on these files will be lost during the update.",
    container_id: 'tempered_files_container',
    content_action: '#',
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
