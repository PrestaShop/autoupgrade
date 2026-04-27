/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import DialogBackup from "../../../views/templates/dialogs/dialog-backup.html.twig";

export default {
  title: "Components/Dialog",
  component: DialogBackup,
};

export const Backup = {
  args: {
    image_included: true,
    form_route_to_confirm_backup: "/",
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
