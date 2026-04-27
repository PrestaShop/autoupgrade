/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import LocalArchive from "../../../views/templates/components/local-archive.html.twig";

export default {
  component: LocalArchive,
  title: "Components/Local archive",
};

export const Default = {
  args: {
    form_fields: {
      archive_zip: "archive_zip",
      archive_xml: "archive_xml",
    },
    archiveFiles: ["backup1.zip", "backup2.zip", "backup3.zip"],
    archiveFileName: "backup1.zip",
    xmlFiles: ["xml1.xml", "xml2.xml", "xml2.xml"],
    xmlFileName: "xml1.xml",
    downloadPath:
      "/var/www/html/admin128ejliho1ih29s5ahu/autoupgrade/download/",
    unableToFindVersion: false,
    unableToFindVersionInXML: false,
    versionsMismatch: false,
    errors: {
      global: "",
      archive_zip: "",
      archive_xml: "",
    },
  },
};
