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

import UpdatePage from "../../../views/templates/pages/update.html.twig";
import LogsViewerJS from "../../../_dev/src/ts/components/LogsViewer";
import { Default as LogsTemplates } from "../components/LogsTemplates.stories";
import { Default as LogsProgress } from "../components/LogsProgress.stories";
import { Default as LogsViewer } from "../components/LogsViewer.stories";
import { Update as Stepper } from "../components/Stepper.stories";

export default {
  component: UpdatePage,
  id: "34",
  title: "Pages/Update",
};

export const Update = {
  args: {
    // Step
    step: {
      code: "update",
      title: "Update",
    },
    logsSummaryWarning: [],
    logsSummaryError: [],
    downloadLogsButtonUrl: "",
    downloadLogsButtonLabel: "",
    download_logs_type: "update",
    step_parent_id: "ua_container",
    stepper_parent_id: "stepper_content",
    backup_available: true,
    restore_route: "restore-page-backup-selection",
    success_route: "update-step-post-update",
    download_logs_route: "download-logs",
    submit_error_report_route: "update-step-update-submit-error-report",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Logs
    ...LogsProgress.args,
    ...LogsViewer.args,
    // Stepper
    ...Stepper.args,
  },
  play: async ({ args }) => {
    const logsViewerElement = document.querySelector(
      "[data-component='logs-viewer']",
    );
    const logsViewer = new LogsViewerJS(logsViewerElement);
    logsViewer.addLogs(LogsTemplates.args.logs);
    logsViewer.displaySummary(
      LogsTemplates.args.logsSummaryWarning,
      LogsTemplates.args.logsSummaryError,
    );
  },
};
