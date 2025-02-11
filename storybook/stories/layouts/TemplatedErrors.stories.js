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

import { routeHandler } from "../../../_dev/src/ts/autoUpgrade";
import DialogContainer from "../../../_dev/src/ts/components/DialogContainer";
import Hydration from "../../../_dev/src/ts/utils/Hydration";
import Layout from "../../../views/templates/layouts/layout.html.twig";

export default {
  component: Layout,
  title: "Layouts/Errors",
  args: {
    psBaseUri: "/",
    assets_base_path: "",
    ps_version: "9.0.0",
    app_parent_id: "update_assistant",
    page_parent_id: "ua_page",
    step_parent_id: "ua_container",
    stepper_parent_id: "stepper_content",
    step: {
      state: "normal",
      title: "Post-update",
      code: "post-update",
    },
    steps: [
      {
        state: "normal",
        title: "Post-update",
        code: "post-update",
      },
    ],
    page: "update",
    dialog_parent_id: DialogContainer.containerId,

    exit_link: "#",
    dev_doc_link: "#",
    data_transparency_link: "#",

    error_template_target: "ua_page",
    exit_to_shop_admin: "#",
    exit_to_app_home: "#",
    submit_error_report_route: "#",
  },
};

export const Error500 = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ code: 500 });
  },
};

export const Error502 = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ code: 502 });
  },
};

export const Timeout = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ type: "ETIMEDOUT" });
  },
};

export const EmptyResponse = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ type: "APP_ERR_RESPONSE_EMPTY" });
  },
};

export const InvalidResponse = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ type: "APP_ERR_RESPONSE_BAD_TYPE" });
  },
};

export const OtherError = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ type: "SOME_CODE_WE_DONT_KNOW" });
  },
};
