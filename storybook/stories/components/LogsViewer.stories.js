/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import LogsViewer from "../../../views/templates/components/logs-viewer.html.twig";

export default {
  component: LogsViewer,
  title: "Components/LogsViewer",
  includeStories: [],
};

export const UpdateLogsViewer = {
  args: {
    downloadLogsButtonUrl: "#",
    downloadLogsButtonLabel: "Download update logs",
    downloadLogsRoute: "/",
    downloadLogsType: "update",
    download_logs_parent_id: "download_logs",
  },
};

export const RestoreLogsViewer = {
  args: {
    downloadLogsButtonUrl: "#",
    downloadLogsButtonLabel: "Download restore logs",
    downloadLogsRoute: "/",
    downloadLogsType: "restore",
    download_logs_parent_id: "download_logs",
  },
};
