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
import SendErrorReportDialog from '../../src/ts/appUI/dialogs/SendErrorReportDialog';
import { logStore } from '../../src/ts/appUI/store/LogStore';
import { FeedbackFields } from '../../src/ts/appUI/types/sentryApi';

// Mock ScriptHandler to prevent side effects
jest.mock('../../src/ts/appUI/routing/ScriptHandler', () => {
  return jest.fn().mockImplementation(() => ({
    loadScript: jest.fn()
  }));
});

// Mock the logStore
jest.mock('../../src/ts/appUI/store/LogStore', () => ({
  logStore: {
    getErrors: jest.fn(),
    getLogs: jest.fn(),
    getWarnings: jest.fn()
  }
}));

// Mock the sentryApi
jest.mock('../../src/ts/appUI/api/sentryApi', () => ({
  sendUserFeedback: jest.fn()
}));

describe('SendErrorReportDialog', () => {
  let dialog: SendErrorReportDialog;

  const lastErrorMessage =
    'HTTP request failed. Route update-page-version-choice - Type: ERR_BAD_RESPONSE - HTTP Code 500';

  const initTemplateAndDialogWithResponse = (response: string) => {
    document.body.innerHTML = `
      <html>
        <body>
          <form id="form-error-feedback">
            <textarea id="errorMessage"></textarea>
            <input name="${FeedbackFields.EMAIL}" value="john.doe@example.com" />
            <textarea name="${FeedbackFields.COMMENTS}">This is a test comment.</textarea>
          </form>
          <div id="log-additional-contents">${response}</div>
        </body>
      </html>
    `;

    (logStore.getErrors as jest.Mock).mockReturnValue([{ message: lastErrorMessage }]);

    dialog = new SendErrorReportDialog();
    dialog.mount();
  };

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('should display the error returned in the JSON', () => {
    const responseContent = {
      nextQuickInfo: [
        'CRITICAL - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 70 - PrestaShop\\Module\\AutoUpgrade\\Exceptions\\DistributionApiException: Error when retrieving data from Distribution API'
      ],
      error: true,
      next: 'error'
    };
    initTemplateAndDialogWithResponse(JSON.stringify(responseContent));

    const errorMessageArea: HTMLTextAreaElement | null = document.querySelector('#errorMessage');
    expect(errorMessageArea).not.toBeNull();
    const expectedValue = `HTTP request failed. Route update-page-version-choice - Type: ERR_BAD_RESPONSE - HTTP Code 500

CRITICAL - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 70 - PrestaShop\\Module\\AutoUpgrade\\Exceptions\\DistributionApiException: Error when retrieving data from Distribution API`;
    expect(errorMessageArea?.value).toBe(expectedValue);
  });

  it('should display several errors returned in the JSON', () => {
    const responseContent = {
      nextQuickInfo: [
        'WARNING - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 67 - file_get_contents(): https:// wrapper is disabled in the server configuration by allow_url_fopen=0',
        'WARNING - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 67 - file_get_contents(https://api.prestashop-project.org/prestashop): Failed to open stream: no suitable wrapper could be found',
        'CRITICAL - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 70 - PrestaShop\\Module\\AutoUpgrade\\Exceptions\\DistributionApiException: Error when retrieving data from Distribution API'
      ],
      error: true,
      next: 'error'
    };
    initTemplateAndDialogWithResponse(JSON.stringify(responseContent));

    const errorMessageArea: HTMLTextAreaElement | null = document.querySelector('#errorMessage');
    expect(errorMessageArea).not.toBeNull();
    const expectedValue = `HTTP request failed. Route update-page-version-choice - Type: ERR_BAD_RESPONSE - HTTP Code 500

WARNING - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 67 - file_get_contents(): https:// wrapper is disabled in the server configuration by allow_url_fopen=0

WARNING - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 67 - file_get_contents(https://api.prestashop-project.org/prestashop): Failed to open stream: no suitable wrapper could be found

CRITICAL - /var/www/html/modules/autoupgrade/classes/Services/DistributionApiService.php line 70 - PrestaShop\\Module\\AutoUpgrade\\Exceptions\\DistributionApiException: Error when retrieving data from Distribution API`;
    expect(errorMessageArea?.value).toBe(expectedValue);
  });

  it('should only display the summary if there is no JSON to parse', () => {
    const responseContent =
      'Oops, something went wrong\n\nTry to refresh this page or feel free to contact us if the problem persists.';
    initTemplateAndDialogWithResponse(responseContent);

    const errorMessageArea: HTMLTextAreaElement | null = document.querySelector('#errorMessage');
    expect(errorMessageArea).not.toBeNull();
    const expectedValue =
      'HTTP request failed. Route update-page-version-choice - Type: ERR_BAD_RESPONSE - HTTP Code 500';
    expect(errorMessageArea?.value).toBe(expectedValue);
  });
});
