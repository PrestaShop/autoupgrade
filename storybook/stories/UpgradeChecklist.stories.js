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

import Checklist from "../../views/templates/block/checklist.html.twig";

export default {
  component: Checklist,
  args: {
    psBaseUri: "/",
    showErrorMessage: true,
    moduleVersion: "5.0.3",
    moduleIsUpToDate: true,
    moduleUpdateLink:
      "/admin/index.php/improve/modules/updates?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    isShopVersionMatchingVersionInDatabase: true,
    adminToken: "e28662287eebd3c6422ff5dd6db836ac",
    informationsLink:
      "/admin/index.php/configure/advanced/system-information/?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    maintenanceLink:
      "/admin/index.php/configure/shop/maintenance/?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    rootDirectoryIsWritable: true,
    rootDirectory: "/var/www/html",
    adminDirectoryIsWritable: true,
    adminDirectoryWritableReport: "",
    safeModeIsDisabled: true,
    allowUrlFopenOrCurlIsEnabled: true,
    zipIsEnabled: true,
    storeIsInMaintenance: false,
    isLocalEnvironment: false,
    currentIndex: "index.php?controller=AdminSelfUpgrade",
    token: "41ce863d4d03e88e0c953d4979d58660",
    cachingIsDisabled: true,
    maxExecutionTime: 0,
    phpRequirementsState: 1,
    phpCompatibilityRange: {
      php_min_version: "7.2.5",
      php_max_version: "8.1",
      php_current_version: "7.4.33",
    },
    checkApacheModRewrite: true,
    notLoadedPhpExtensions: [],
    checkKeyGeneration: true,
    checkMemoryLimit: true,
    checkFileUploads: true,
    notExistsPhpFunctions: [],
    checkPhpSessions: true,
    missingFiles: [],
    notWritingDirectories: [],
  },
};

export const Default = {};
