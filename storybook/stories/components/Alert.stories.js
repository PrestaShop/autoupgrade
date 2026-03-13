/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
    message:
      "The requirements check is complete, you can update your store to this version of PrestaShop.",
    alertStatus: "success",
  },
};

export const AlertWithForm = {
  args: {
    title: "Update failed",
    message:
      "Your store may not work properly anymore. Select the backup you want to use and restore it to avoid any data loss.",
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
    message:
      "It’s available at /your-admin-directory/autoupgrade/backup. You're ready to start the update now.",
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
    message:
      "It’s available at admin/autoupgrade/backup. You're ready to start the update now.",
    alertStatus: "success",
  },
};
