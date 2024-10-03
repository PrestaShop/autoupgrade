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

import VersionChoicePage from "../../../views/templates/pages/update.html.twig";
import LocalArchive from "../components/LocalArchive.stories";
import RadioCard from "../components/RadioCard.stories";

export default {
  component: VersionChoicePage,
  title: "Pages/Update",
  args: {
    ...RadioCard.args,
    ...LocalArchive.args,
    steps: [
      {
        state: "current",
        title: "Version choice",
      },
      {
        state: "normal",
        title: "Update options",
      },
      {
        state: "normal",
        title: "Backup",
      },
      {
        state: "normal",
        title: "Update",
      },
      {
        state: "normal",
        title: "Post-update",
      },
    ],
    step: {
      code: "version-choice",
      title: "Version choice",
    },
    psBaseUri: "/",
    assetsBasePath: "/",
    up_to_date: false,
    no_local_archive: true,
    current_prestashop_version: "8.1.6",
    current_php_version: "8.1",
    next_release: {
      version: "9.0.0",
      badge_label: "Major version",
      badge_status: "major",
      release_note: "https://github.com/PrestaShop/autoupgrade"
    },
    local_archives: {
      zip: ["prestashop.zip"],
      xml: ["prestashop.xml"],
    },
    assets_base_path: "",
    step_parent_id: "ua_container",
    radio_card_online_parent_id: "radio_card_online",
    radio_card_archive_parent_id: "radio_card_archive",
  },
};

export const VersionChoice = {};
