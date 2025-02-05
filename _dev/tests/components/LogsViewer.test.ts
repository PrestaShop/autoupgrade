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
import { AxiosError } from 'axios';
import LogsViewer from '../../src/ts/components/LogsViewer';
import { logStore } from '../../src/ts/store/LogStore';

// add this mock to avoid unnecessary error
jest.mock('../../src/ts/routing/ScriptHandler', () => {
  return jest.fn().mockImplementation(() => ({
    loadScript: jest.fn()
  }));
});

describe('LogsViewer', () => {
  let logsViewer: LogsViewer;
  let container: HTMLElement;

  beforeEach(() => {
    jest.spyOn(console, 'error').mockImplementation(() => {});
    logStore.clearLogs();

    container = document.createElement('div');
    container.innerHTML = `
      <div data-component="logs-viewer" class="logs__inner">
        <form id="form-logs-download-button" data-download-logs-route="">
          <input type="hidden" name="download-logs-type" value="" />
        </form>
        <div data-slot-component="scroll" class="logs__scroll" tabindex="0">
          <div data-slot-component="list" class="logs__list"></div>
        </div>
        <div data-slot-component="summary" class="logs__summaries"></div>
        <pre id="log-additional-contents" class="hidden"></pre>
      </div>
      <template id="log-line">
        <div class="logs__line">
          <div class="logs__line-content"></div>
        </div>
      </template>
      
      <template id="log-summary">
        <div class="logs__summary">
          <div class="logs__summary-top">
            <p data-slot-template="title" class="logs__summary-title h3"></p>
            <span data-slot-template="count" class="logs__summary-total badge badge-danger"></span>
          </div>
          <div class="logs__summary-scroll"></div>
        </div>
      </template>
      
      <template id="summary-warning-title">Warning summary</template>
      <template id="summary-error-title">Error summary</template>
      
      <template id="summary-warning-link">
        <a class="logs__summary-anchor link">See warning</a>
      </template>
      <template id="summary-error-link">
        <a class="logs__summary-anchor link">See error</a>
      </template>

      <template id="error-page-template">
        <div class="error-page__desc">
          <div class="error-page__desc-404 hidden">
            <p>The requested page or resource could not be found. This might be due to:</p>
            <ul>
              <li>A broken or outdated link.</li>
              <li>The page being moved or deleted.</li>
              <li>A typo in the URL.</li>
            </ul>
          </div>
        </div>
      </template>
    `;
    document.body.appendChild(container);
    logsViewer = new LogsViewer(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  describe('addLogs', () => {
    it('should add logs to the list', () => {
      logsViewer.addLogs([
        'INFO - Info message',
        'WARNING - Warning message',
        'ERROR - Error message'
      ]);

      const logsList = container.querySelector('[data-slot-component="list"]');
      const logLines = logsList!.querySelectorAll('.logs__line');

      expect(logLines.length).toBe(3);
      expect(logLines[0].textContent).toBe('Info message');
      expect(logLines[0].classList.contains('logs__line--success')).toBe(true);

      expect(logLines[1].textContent).toBe('Warning message');
      expect(logLines[1].classList.contains('logs__line--warning')).toBe(true);

      expect(logLines[2].textContent).toBe('Error message');
      expect(logLines[2].classList.contains('logs__line--error')).toBe(true);
    });

    it('should prevent adding logs when the summary is displayed', async () => {
      const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();

      const logsList = container.querySelector('[data-slot-component="list"]');
      const logLines = logsList!.querySelectorAll('.logs__line');

      expect(logLines.length).toBe(0);

      await logsViewer.displaySummary();
      logsViewer.addLogs(['INFO - Log message']);

      expect(consoleSpy).toHaveBeenCalledWith('Cannot display summary because logs are empty');
      expect(logLines.length).toBe(0);

      consoleSpy.mockRestore();
    });
  });

  describe('displaySummary', () => {
    it('should create a summary with grouped logs by severity', async () => {
      logsViewer.addLogs([
        'WARNING - First warning',
        'ERROR - First error',
        'WARNING - Second warning'
      ]);
      await logsViewer.displaySummary();

      const summaryContainer = container.querySelector('[data-slot-component="summary"]');
      expect(summaryContainer).not.toBeNull();

      const summaries = summaryContainer!.querySelectorAll('.logs__summary');
      expect(summaries.length).toBe(2);

      const warningSummary = summaries[0];
      const warningTitle = warningSummary.querySelector('[data-slot-template="title"]');
      const warningChildren = warningSummary.querySelectorAll('.logs__line');

      expect(warningTitle).not.toBeNull();
      expect(warningTitle!.textContent!.trim()).toBe('Warning summary');

      expect(warningChildren.length).toBe(2);
      expect(warningChildren[0].textContent).toContain('First warning');
      expect(warningChildren[1].textContent).toContain('Second warning');

      const errorSummary = summaries[1];
      const errorTitle = errorSummary.querySelector('[data-slot-template="title"]');
      const errorChildren = errorSummary.querySelectorAll('.logs__line');

      expect(errorTitle).not.toBeNull();
      expect(errorTitle!.textContent!.trim()).toBe('Error summary');

      expect(errorChildren.length).toBe(1);
      expect(errorChildren[0].textContent).toContain('First error');
    });

    it('should not display summary if no logs are present', async () => {
      const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();
      await logsViewer.displaySummary();

      const summary = container.querySelector('[data-slot-component="summary"]');
      expect(summary!.children.length).toBe(0);
      expect(consoleSpy).toHaveBeenCalledWith('Cannot display summary because logs are empty');

      consoleSpy.mockRestore();
    });
  });

  describe('addError', () => {
    beforeEach(() => {
      jest.spyOn(logsViewer, 'addLogs').mockImplementation(() => {});
    });

    it('should add the detailed and generic error messages', () => {
      logsViewer.addError({
        type: 'IRRELEVANT_CODE',
        code: 404,
      });

      expect(logsViewer.addLogs).toHaveBeenCalledTimes(2);
      expect(logsViewer.addLogs).toHaveBeenNthCalledWith(1, [`ERROR - 
            The requested page or resource could not be found. This might be due to:
              A broken or outdated link.
              The page being moved or deleted.
              A typo in the URL.`]);
      expect(logsViewer.addLogs).toHaveBeenNthCalledWith(2, ['ERROR - HTTP request failed. Type: IRRELEVANT_CODE - HTTP Code: 404']);
      expect((container.querySelector('#log-additional-contents') as HTMLPreElement|null)?.innerText).toBeUndefined();
    });

    it('should only add the generic error message', () => {
      logsViewer.addError({
        type: 'SOME_ERROR_REASON_WE_HAVE_NOT_DETAILED',
        code: 200,
      });

      expect(logsViewer.addLogs).toHaveBeenCalledTimes(1);
      expect(logsViewer.addLogs).toHaveBeenNthCalledWith(1, ['ERROR - HTTP request failed. Type: SOME_ERROR_REASON_WE_HAVE_NOT_DETAILED - HTTP Code: 200']);
      expect((container.querySelector('#log-additional-contents') as HTMLPreElement|null)?.innerText).toBeUndefined();
    });

    it('should fallback when no data is provided', () => {
      logsViewer.addError({});

      expect(logsViewer.addLogs).toHaveBeenCalledTimes(1);
      expect(logsViewer.addLogs).toHaveBeenNthCalledWith(1, ['ERROR - HTTP request failed. Type: N/A - HTTP Code: N/A']);
      expect((container.querySelector('#log-additional-contents') as HTMLPreElement|null)?.innerText).toBeUndefined();
    });

    it('should add the response contents in the block when provided', () => {
      logsViewer.addError({
        type: AxiosError.ERR_BAD_RESPONSE,
        code: 500,
        additionalContents: 'Oh no!'
      });

      expect(logsViewer.addLogs).toHaveBeenCalledTimes(1);
      expect(logsViewer.addLogs).toHaveBeenNthCalledWith(1, ['ERROR - HTTP request failed. Type: ERR_BAD_RESPONSE - HTTP Code: 500']);
      expect((container.querySelector('#log-additional-contents') as HTMLPreElement|null)?.innerText).toBe('Oh no!');
    });
  });
});
