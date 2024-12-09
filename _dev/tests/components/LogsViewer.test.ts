import LogsViewer from '../../src/ts/components/LogsViewer';

describe('LogsViewer', () => {
  let logsViewer: LogsViewer;
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    container.innerHTML = `
      <div data-component="logs-viewer" class="logs__inner">
        <div data-slot-component="scroll" class="logs__scroll" tabindex="0">
          <div data-slot-component="list" class="logs__list"></div>
        </div>
        <div data-slot-component="summary" class="logs__summaries"></div>
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

    it('should prevent adding logs when the summary is displayed', () => {
      const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();

      const logsList = container.querySelector('[data-slot-component="list"]');
      const logLines = logsList!.querySelectorAll('.logs__line');

      expect(logLines.length).toBe(0);

      logsViewer.displaySummary();
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

    it('should not display summary if no logs are present', () => {
      const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();
      logsViewer.displaySummary();

      const summary = container.querySelector('[data-slot-component="summary"]');
      expect(summary!.children.length).toBe(0);
      expect(consoleSpy).toHaveBeenCalledWith('Cannot display summary because logs are empty');

      consoleSpy.mockRestore();
    });
  });
});
