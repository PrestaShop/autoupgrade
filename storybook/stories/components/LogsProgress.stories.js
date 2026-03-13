/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import LogsProgress from "../../../views/templates/components/logs-summary.html.twig";

export default {
  component: LogsProgress,
  title: "Components/Logs progress",
};

export const UpdateLogsProgress = {
  args: {
    progressStatus: "Update in progress",
    progressPercentage: 25,
  },
  play: async ({ args }) => {
    const textSlots = document.querySelectorAll("[data-slot-component='text']");
    textSlots.forEach((slot) => {
      slot.textContent = args.progressStatus;
    });
  },
};

export const RestoreLogsProgress = {
  args: {
    progressStatus: "Restoration in progress",
    progressPercentage: 45,
  },
  play: async ({ args }) => {
    const textSlots = document.querySelectorAll("[data-slot-component='text']");
    textSlots.forEach((slot) => {
      slot.textContent = args.progressStatus;
    });
  },
};
