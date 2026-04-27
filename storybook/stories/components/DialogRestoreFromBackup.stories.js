/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
      backup_name: "backup_name",
    },
  },
};

export const RestoreFromBackup = {
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
