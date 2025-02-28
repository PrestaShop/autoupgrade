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

import LogsViewer from "../../../views/templates/components/logs-viewer.html.twig";

export default {
  component: LogsViewer,
  title: "Components/LogsViewer",
};

export const Default = {
  args: {
    logs: [
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "warning",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "warning_1",
      },
      {
        status: "error",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "error_1",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "warning",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "warning_2",
      },
      {
        status: "error",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "error_2",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
    ],
    logsSummaryWarning: [
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "warning_1",
      },
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "warning_2",
      },
    ],
    logsSummaryError: [
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "error_1",
      },
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "error_2",
      },
    ],
    downloadLogsButtonUrl: "#",
    downloadLogsButtonLabel: "Download update logs",
    downloadLogsRoute: "/",
    downloadLogsType: "update",
    download_logs_parent_id: "download_logs"
  },
};

export const RestoreLogsViewer = {
  args: {
    logs: [
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "warning",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "warning_1",
      },
      {
        status: "error",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "error_1",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "warning",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "warning_2",
      },
      {
        status: "error",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        id: "error_2",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
      {
        status: "success",
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
      },
    ],
    logsSummaryWarning: [
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "warning_1",
      },
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "warning_2",
      },
    ],
    logsSummaryError: [
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "error_1",
      },
      {
        message: "src/Core/Domain/Product/Pack/Query/GetPackedProducts.php added to archive. 13114 files left.",
        anchor: "error_2",
      },
    ],
    downloadLogsButtonUrl: "#",
    downloadLogsButtonLabel: "Download restore logs",
    downloadLogsRoute: "/",
    downloadLogsType: "restore",
    download_logs_parent_id: "download_logs"
  },
};
