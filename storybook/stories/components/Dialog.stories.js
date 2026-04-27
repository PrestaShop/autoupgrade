/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import Dialog from "../../../views/templates/components/dialog.html.twig";

export default {
  title: "Components/Dialog",
  component: Dialog,
  excludeStories: ["Default"],
};

export const Default = {
  args: {
    assets_base_path: "",
  },
};
