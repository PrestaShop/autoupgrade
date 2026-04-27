/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import DialogConfirmModuleManager from "../../../views/templates/dialogs/dialog-confirm-module-manager.html.twig";

export default {
  title: "Components/Dialog",
  component: DialogConfirmModuleManager,
};

export const ConfirmModuleManager = {
  args: {
    form_route_to_confirm_module_manager_dialog: "/",
    module_manager_link: "/",
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
