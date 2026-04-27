/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import DialogErrorReport from "../../../views/templates/dialogs/dialog-error-report.html.twig";

export default {
  title: "Components/Dialog",
  component: DialogErrorReport,
};

export const ErrorReport = {
  args: {
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
