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

import UpgradeButton from "../../views/templates/block/upgradeButtonBlock.html.twig";
import ChannelInfo from "./ChannelInfo.stories";

export default {
  component: UpgradeButton,
  args: {
    versionCompare: -1,
    currentPsVersion: "8.0.5",
    latestChannelVersion: "8 stable - (8.1.7)",
    channel: "minor",
    showUpgradeButton: false,
    upgradeLink: "",
    showUpgradeLink: false,
    changelogLink: "",
    skipActions: [],
    lastVersionCheck: false,
    token: "64e10c9ef64f54c44d510fe41bcf4328",
    channelOptions: [
      { 0: "useMajor", 1: "major", 2: "Major release" },
      { 0: "useMinor", 1: "minor", 2: "Minor release (recommended)" },
      { 0: "useRC", 1: "rc", 2: "Release candidates" },
      { 0: "useBeta", 1: "beta", 2: "Beta releases" },
      { 0: "useAlpha", 1: "alpha", 2: "Alpha releases" },
      {
        0: "usePrivate",
        1: "private",
        2: "Private release (require link and MD5 hash)",
      },
      { 0: "useArchive", 1: "archive", 2: "Local archive" },
      { 0: "useDirectory", 1: "directory", 2: "Local directory" },
    ],
    privateChannel: {
      releaseLink: "",
      releaseMd5: "",
      allowMajor: "",
    },
    archiveFiles: [],
    xmlFiles: [],
    archiveFileName: "prestashop.zip",
    xmlFileName: "",
    archiveVersionNumber: "",
    downloadPath:
      "/var/www/html/admin128ejliho1ih29s5ahu/autoupgrade/download/",
    directoryVersionNumber: "",
    manualMode: false,
    phpVersion: "7.4.33",
    ...ChannelInfo.args,
  },
};
