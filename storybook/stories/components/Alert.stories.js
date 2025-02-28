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

import Alert from "../../../views/templates/components/alert.html.twig";

export default {
  component: Alert,
  title: "Components/Alert",
  id: "1",
  includeStories: ["Default", "AlertWithForm", "AlertWithLink"],
  argTypes: {
    alertStatus: {
      control: "select",
      options: ["info", "success", "warning", "danger"],
    },
  },
};

export const Default = {
  args: {
    title: "",
    message: "The requirements check is complete, you can update your store to this version of PrestaShop.",
    alertStatus: "success",
  },
};

export const AlertWithForm = {
  args: {
    title: "Update failed",
    message: "Your store may not work properly anymore. Select the backup you want to use and restore it to avoid any data loss.",
    alertStatus: "warning",
    // Required for form
    buttonLabel: "Restore",
    formRoute: "/",
    formName: "alert-form",
  },
};

export const AlertWithLink = {
  args: {
    title: "Backup completed",
    message: "It’s available at /your-admin-directory/autoupgrade/backup. You're ready to start the update now.",
    alertStatus: "success",
    buttonDownload: "backup.log",
    // Required for link
    buttonLabel: "Download backup logs",
    buttonUrl: "#",
  },
};

export const NoLocalArchive = {
  args: {
    title: "",
    message: "It’s available at admin/autoupgrade/backup. You're ready to start the update now.",
    alertStatus: "success",
  },
};

