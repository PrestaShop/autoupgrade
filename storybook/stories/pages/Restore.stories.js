/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import RestorePage from "../../../views/templates/pages/restore.html.twig";
import LogsViewer from "../../../_dev/src/ts/appUI/components/LogsViewer";
import { Default as LogsTemplates } from "../components/LogsTemplates.stories";
import { RestoreLogsProgress } from "../components/LogsProgress.stories";
import { RestoreLogsViewer } from "../components/LogsViewer.stories";
import { Restore as Stepper } from "../components/Stepper.stories";

export default {
  component: RestorePage,
  id: "41",
  title: "Pages/Restore",
};

export const Restore = {
  args: {
    // Step
    step: {
      code: "restore",
      title: "Restore",
    },
    step_parent_id: "ua_container",
    download_logs_route: "download-logs",
    download_logs_type: "restore",
    submit_error_report_route: "update-step-update-submit-error-report",
    try_again_route: "restore-page-backup-selection",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Logs
    ...RestoreLogsProgress.args,
    ...RestoreLogsViewer.args,
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
