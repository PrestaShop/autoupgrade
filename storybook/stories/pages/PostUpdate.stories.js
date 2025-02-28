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
    data_transparency_link: "https://www.prestashop-project.org/data-transparency",
    // Stepper
    ...Stepper.args
  },
};
