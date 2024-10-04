/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

import RadioCardOnline from "../../../views/templates/components/radio-card-online.html.twig";
import CheckRequirements from "./CheckRequirements.stories";

export default {
  component: RadioCardOnline,
  title: "Components/Radio card",
  argTypes: {
    badgeStatus: {
      control: "select",
      options: ["major", "minor", "patch"],
    },
  },
  args: {
    ...CheckRequirements.args,
  },
};

export const Online = {
  args: {
    radioCardId: "canal_choice",
    radioName: "canal_choice",
    radioValue: "online",
    checked: false,
    title: "Local archive",
    message: "The maximum version of PrestaShop to which you can upgrade your store, based on its PHP version.",
    disabled: false,
    disabledMessage: "",
    badgeLabel: "Major version",
    badgeStatus: "major",
    releaseNote: "https://github.com/PrestaShop/autoupgrade",
    enableRequirementsCheck: true,
    ...CheckRequirements.args,
  },
};
