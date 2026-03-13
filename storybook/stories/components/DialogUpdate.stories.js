/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import DialogUpdate from "../../../views/templates/dialogs/dialog-update.html.twig";
import { Default as Dialog } from "./Dialog.stories";

export default {
  title: "Components/Dialog",
  component: DialogUpdate,
};

export const Update = {
  args: {
    ...Dialog.args,
    backup_completed: true,
    form_route_to_confirm: "/",
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
