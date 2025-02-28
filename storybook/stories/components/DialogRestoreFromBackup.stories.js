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

import DialogRestoreFromBackup from "../../../views/templates/dialogs/dialog-restore-from-backup.html.twig";

export default {
  title: "Components/Dialog",
  component: DialogRestoreFromBackup,
  args: {
    backup_version: "1.7.8.1",
    backup_name: "backup-name",
    backup_date: "02/28/25 12:16:10",
    form_name: "backup_to_restore",
    form_route_to_confirm_restore: "/",
    form_fields: {
      BACKUP_NAME: "backup_name",
    },
  },
};

export const RestoreFromBackup = {
  play: async () => {
    const dialog = document.querySelector('.dialog');
    dialog.showModal();
  },
};
