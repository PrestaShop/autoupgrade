/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import UpdatePage from "../../../views/templates/pages/update.html.twig";
import LogsViewer from "../../../_dev/src/ts/appUI/components/LogsViewer";
import { Default as LogsTemplates } from "../components/LogsTemplates.stories";
import { UpdateLogsProgress } from "../components/LogsProgress.stories";
import { UpdateLogsViewer } from "../components/LogsViewer.stories";
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
    ...UpdateLogsProgress.args,
    ...UpdateLogsViewer.args,
    // Stepper
    ...Stepper.args,
  },
  play: async ({ args }) => {
    const logsViewerElement = document.querySelector(
      "[data-component='logs-viewer']",
    );
    const logsViewer = new LogsViewer(logsViewerElement);
    logsViewer.addLogs(LogsTemplates.args.logs);
    logsViewer.displaySummary(
      LogsTemplates.args.logsSummaryWarning,
      LogsTemplates.args.logsSummaryError,
    );
  },
};
