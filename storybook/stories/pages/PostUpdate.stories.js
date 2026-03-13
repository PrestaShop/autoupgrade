/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import PostUpdatePage from "../../../views/templates/pages/update.html.twig";
import { PostUpdate as Stepper } from "../components/Stepper.stories";

export default {
  component: PostUpdatePage,
  id: "35",
  title: "Pages/Update",
};

export const PostUpdate = {
  args: {
    // Step
    step: {
      code: "post-update",
      title: "Post-update checklist",
    },
    step_parent_id: "ua_container",
    exit_link: "#",
    dev_doc_link: "#",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    admin_url: "#",
    ps_version: "9.0.0",
    form_route_to_confirm_module_manager_dialog: "#",
    // Stepper
    ...Stepper.args,
  },
};
