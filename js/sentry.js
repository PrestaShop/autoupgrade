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

Sentry.init({
  dsn: "https://eae192966a8d79509154c65c317a7e5d@o298402.ingest.us.sentry.io/4507254110552064",
  release: "v" + input.autoupgrade.version,
  beforeSend(event, hint) {
    // Only the one we handle via the feedback modal must be sent.
    if (!event.tags?.source || event.tags.source !== "feedbackModal") {
      return null;
    }
    event.request.url = maskSensitiveInfoInUrl(
      event.request.url,
      input.adminUrl,
    );

    hint.attachments = [
      { filename: "log.txt", data: readLogPanel("quickInfo") },
      { filename: "error.txt", data: readLogPanel("infoError") },
    ];

    return event;
  },
});

document
  .getElementById("submitErrorReport")
  .addEventListener("click", function () {
    const errorsElements = document.getElementById("infoError");
    if (errorsElements) {
      const childNodes = errorsElements.childNodes;
      let messages = "";
      childNodes.forEach((node) => {
        if (node.nodeType === Node.TEXT_NODE) {
          messages += node.textContent.trim() + "\n";
        }
      });

      const url = maskSensitiveInfoInUrl(window.location.href, input.adminUrl);

      Sentry.setTag("url", url);
      Sentry.setTag("source", "feedbackModal");

      const eventId = Sentry.captureMessage(messages, "error");
      const userEmail = document.getElementById("userEmail");
      const errorDescription = document.getElementById("errorDescription");

      Sentry.captureUserFeedback({
        event_id: eventId,
        email: userEmail.value,
        comments: errorDescription.value,
      });

      // Clean-up
      userEmail.value = "";
      errorDescription.value = "";
      Sentry.setTag("source", "");

      $("#errorModal").modal("hide");
    }
  });

function maskSensitiveInfoInUrl(url, adminFolder) {
  let regex = new RegExp(adminFolder, "g");
  url = url.replace(regex, "/********");

  regex = new RegExp("token=[^&]*", "i");
  return url.replace(regex, "token=********");
}

function readLogPanel(targetPanel) {
  return document.getElementById(targetPanel).innerText;
}
