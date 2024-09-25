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

import CheckRequirements from "../../../views/templates/components/check-requirements.html.twig";

export default {
  component: CheckRequirements,
  title: "Components/Check requirements",
  args: {
    requirementsOk: true,
    checkingForRequirements: true,
    updateAssistantDocs: 
      "https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/use-autoupgrade-module/",
    moduleIsUpToDate: false,
    moduleUpdateLink:
      "/admin/index.php/improve/modules/updates?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    noMissingFiles: false,
    informationLink: 
      "/admin/index.php/configure/advanced/system-information/?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    phpRequirementsState: 1,
    phpCompatibilityRange: {
      php_min_version: "7.2.5",
      php_max_version: "8.1",
      php_current_version: "7.4.33",
    },
    rootDirectoryIsWritable: false,
    rootDirectory: "/var/www/html",
    adminDirectoryIsWritable: false,
    adminDirectoryWritableReport: "", // Not used
    safeModeIsDisabled: false,
    allowUrlFopenOrCurlIsEnabled: false,
    zipIsEnabled: false,
    isLocalEnvironment: false,
    storeIsInMaintenance: false,
    maintenanceLink:
      "/admin/index.php/configure/shop/maintenance/?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    cachingIsDisabled: false,
    cacheLink:
      "/admin/index.php/configure/advanced/performance/?_token=MbkhzPrMc9ZknpvSTqeKbKiekftXvp4IowAxrW4Jcfw",
    maxExecutionTime: 5,
    checkApacheModRewrite: false,
    notLoadedPhpExtensions: [
      "curl", "dom", "fileinfo", "gd", "intl", "json", "mbstring", "openssl", "pdo_mysql", "simplexml", "zip"
    ],
    notExistsPhpFunctions: [
      "fopen", "fclose", "fread", "fwrite", "rename", "file_exists", "unlink", "rmdir", "mkdir", "getcwd", "chdir", "chmod"
    ],
    checkMemoryLimit: false,
    checkFileUploads: false,
    checkKeyGeneration: false,
    notWritingDirectories: [
      "autoupgrade", "views", "js", "css"
    ],
    isShopVersionMatchingVersionInDatabase: false,
  },
};

export const Default = {};
