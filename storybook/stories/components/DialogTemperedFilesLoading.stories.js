/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
