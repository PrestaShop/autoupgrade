/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import PostRestorePage from "../../../views/templates/pages/restore.html.twig";
import { PostRestore as Stepper } from "../components/Stepper.stories";

export default {
  component: PostRestorePage,
  id: "42",
  title: "Pages/Restore",
};

export const PostRestore = {
  args: {
    // Step
    step: {
      code: "post-restore",
      title: "Post-restore checklist",
    },
    step_parent_id: "ua_container",
    exit_link: "#",
    dev_doc_link: "#",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Stepper
    ...Stepper.args,
  },
};
