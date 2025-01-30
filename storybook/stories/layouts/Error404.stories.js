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
import ErrorPage from "../../../_dev/src/ts/pages/ErrorPage";
import ErrorCode404 from "../../../views/templates/pages/errors/404.html.twig";

export default {
  component: ErrorCode404,
  title: "Layouts/Errors",
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
