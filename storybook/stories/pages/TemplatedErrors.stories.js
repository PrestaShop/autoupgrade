/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import DialogContainer from "../../../_dev/src/ts/appUI/components/DialogContainer";
import Hydration from "../../../_dev/src/ts/appUI/utils/Hydration";
import Layout from "../../../views/templates/layouts/layout.html.twig";

export default {
  component: Layout,
  title: "Pages/Errors",
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
    form_route_to_confirm_module_manager_dialog: "#",

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

export const Error560 = {
  play: async ({ canvasElement }) => {
    new Hydration().hydrateError({ code: 560 });
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
