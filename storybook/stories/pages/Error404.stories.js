/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import { routeHandler } from "../../../_dev/src/ts/appUI/main";
import ErrorPage from "../../../_dev/src/ts/appUI/pages/ErrorPage";
import ErrorCode404 from "../../../views/templates/pages/errors/404.html.twig";

export default {
  component: ErrorCode404,
  title: "Pages/Errors",
  args: {
    psBaseUri: "/",
    error_code: "404",
    assets_base_path: "",

    exit_to_shop_admin: "#",
    exit_to_app_home: "#",
  },
};

export const Error404OnHomePage = {
  play: async ({ canvasElement }) => {
    routeHandler.setNewRoute("home-page");
    new ErrorPage().mount();
  },
};
export const Error404 = {
  play: async ({ canvasElement }) => {
    routeHandler.setNewRoute("any-other-page");
    new ErrorPage().mount();
  },
};
