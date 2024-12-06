import ComponentAbstract from './ComponentAbstract';
import { SeverityClasses, LogEntry } from '../types/logsTypes';
import { parseLogWithSeverity } from '../utils/logsUtils';
import { Destroyable } from '../types/DomLifecycle';
import api from '../api/RequestHandler';

export default class LogsViewer extends ComponentAbstract implements Destroyable {
  #warnings: string[] = [];
  #errors: string[] = [];
  #isSummaryDisplayed: boolean = false;

  #timeLastReflow: number = 0;

  #logsList = this.queryElement<HTMLDivElement>(
    '[data-slot-component="list"]',
    'Logs list not found'
  );
  #logsScroll = this.queryElement<HTMLDivElement>(
    '[data-slot-component="scroll"]',
    'Logs scroll not found'
  );
  #logsSummary = this.queryElement<HTMLDivElement>(
    '[data-slot-component="summary"]',
    'Logs summary not found'
  );
  #templateLogLine = this.queryElement<HTMLTemplateElement>(
    '#log-line',
    'Template log line not found'
  );
  #templateSummary = this.queryElement<HTMLTemplateElement>(
    '#log-summary',
    'Template summary not found'
  );

  public beforeDestroy = () => {
    this.#logsSummary.removeEventListener('click', this.#handleLinkEvent);
  };

  /**
   * @public
   * @param {string[]} logs - Array of log strings to be added.
   * @description Adds logs to the viewer and updates the DOM with log lines.
   * Logs with specific severity (WARNING, ERROR) are tracked with unique IDs.
   * Prevents adding logs if the summary is already displayed.
   */
  public addLogs = (logs: string[]): void => {
    if (this.#isSummaryDisplayed) {
      console.warn('Cannot add logs while the summary is displayed');
      return;
    }

    const fragment = document.createDocumentFragment();

    logs.forEach((log) => {
      const logEntry = parseLogWithSeverity(log);
      const logLine = this.#createLogLine(logEntry);

      if (logEntry.className === SeverityClasses.WARNING) {
        const id = `warning-${this.#warnings.length}`;
        this.#warnings.push(id);
        logLine.id = id;
      }

      if (logEntry.className === SeverityClasses.ERROR) {
        const id = `error-${this.#errors.length}`;
        this.#errors.push(id);
        logLine.id = id;
      }

      fragment.appendChild(logLine);
    });

    this.#appendFragmentElement(fragment, this.#logsList);
    this.#scrollToBottom();
  };

  /**
   * @public
   * @description Displays a summary of logs, grouping warnings and errors.
   * Summaries include links to the corresponding log lines.
   * Adds a click event listener to handle navigation within the summary.
   * Prevents displaying a summary if no logs are present.
   */
  public displaySummary = async (): Promise<void> => {
    if (!this.#logsList.hasChildNodes()) {
      console.warn('Cannot display summary because logs are empty');
      return;
    }

    const fragment = document.createDocumentFragment();

    if (this.#warnings.length > 0) {
      const warningsSummary = this.#createSummary(SeverityClasses.WARNING, this.#warnings);
      fragment.appendChild(warningsSummary);
    }

    if (this.#errors.length > 0) {
      const errorsSummary = this.#createSummary(SeverityClasses.ERROR, this.#errors);
      fragment.appendChild(errorsSummary);
    }

    if (fragment.hasChildNodes()) {
      this.#logsSummary.addEventListener('click', this.#handleLinkEvent);
    }

    await api.post(this.element.dataset.downloadLogsRoute!);

    this.#appendFragmentElement(fragment, this.#logsSummary);
    this.#isSummaryDisplayed = true;
  };

  /**
   * @private
   * @param {LogEntry} logEntry - Parsed log entry containing message and severity information.
   * @returns {HTMLDivElement} - The created log line element.
   * @description Creates an HTML log line element based on the log entry's severity and message.
   * Applies appropriate CSS classes and data attributes to the log line.
   */
  #createLogLine = (logEntry: LogEntry): HTMLDivElement => {
    const logLineFragment = this.#templateLogLine.content.cloneNode(true) as DocumentFragment;
    const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;

    logLine.classList.add(`logs__line--${logEntry.className}`);
    logLine.setAttribute('data-status', logEntry.className);
    logLine.textContent = logEntry.message;

    return logLine;
  };

  /**
   * @private
   * @param {DocumentFragment} fragment - The fragment containing child elements to append.
   * @param {HTMLElement} element - The target element to which the fragment will be appended.
   * @description Appends a document fragment to a specified HTML element in the DOM.
   */
  #appendFragmentElement = (fragment: DocumentFragment, element: HTMLElement) => {
    element.appendChild(fragment);
  };

  /**
   * @private
   * @description Automatically scrolls the logs container to the bottom of the list.
   */
  #scrollToBottom = () => {
    const currentTime = Date.now();

    // We defer scrolls to the bottom if they are happening too frequently
    if (currentTime - this.#timeLastReflow > 500) {
      this.#timeLastReflow = currentTime;
      this.#logsScroll.scrollTop = this.#logsScroll.scrollHeight;
    }
  };

  /**
   * @private
   * @param {SeverityClasses} severity - The severity type (e.g., WARNING, ERROR).
   * @param {string[]} logs - Array of log IDs to include in the summary.
   * @returns {HTMLDivElement} - The created summary element.
   * @description Creates a summary element grouping logs by severity.
   * Each log in the summary includes a link to its corresponding log line.
   */
  #createSummary(severity: SeverityClasses, logs: string[]): HTMLDivElement {
    const summaryFragment = this.#templateSummary.content.cloneNode(true) as DocumentFragment;
    const summary = summaryFragment.querySelector('.logs__summary') as HTMLDivElement;
    const summaryScroll = summaryFragment.querySelector('.logs__summary-scroll') as HTMLDivElement;

    const title = this.#getSummaryTitle(severity);
    const titleContainer = summary.querySelector('[data-slot-template="title"]') as HTMLDivElement;
    titleContainer.textContent = title;

    const countContainer = summary.querySelector('[data-slot-template="count"]') as HTMLDivElement;
    countContainer.textContent = String(logs.length);

    const linkElement = this.#createSummaryLinkElement(severity);

    logs.forEach((logId) => {
      const logElement = document.getElementById(logId)!;
      const cloneLogElement = logElement.cloneNode(true) as HTMLDivElement;
      cloneLogElement.id = '';

      const linkClone = linkElement.cloneNode(true) as HTMLAnchorElement;
      linkClone.href = `#${logId}`;

      cloneLogElement.appendChild(linkClone);

      summaryScroll.appendChild(cloneLogElement);
    });

    return summary;
  }

  /**
   * @private
   * @param {SeverityClasses} severity - The severity type (e.g., WARNING, ERROR).
   * @returns {string} - The content of the title template.
   * @description Retrieves the title template for the given severity type and extracts its content.
   */
  #getSummaryTitle(severity: SeverityClasses): string {
    const titleTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-title`,
      `Summary ${severity} title not found`
    );

    const title = titleTemplate.content.cloneNode(true) as HTMLElement;

    return title.textContent!;
  }

  /**
   * @private
   * @param {SeverityClasses} severity - The severity type (e.g., WARNING, ERROR).
   * @returns {HTMLAnchorElement} - The created link element.
   * @description Creates a link element from the template corresponding to the given severity type.
   */
  #createSummaryLinkElement(severity: SeverityClasses): HTMLAnchorElement {
    const linkTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-link`,
      `Summary ${severity} link not found`
    );

    const linkFragment = linkTemplate.content.cloneNode(true) as DocumentFragment;
    return linkFragment.querySelector('.link') as HTMLAnchorElement;
  }

  /**
   * @private
   * @param {MouseEvent} event - The click event object.
   * @description Handles click events on summary links to scroll to the corresponding log line.
   * Highlights the target log line briefly for visual focus.
   */
  #handleLinkEvent = (event: MouseEvent): void => {
    const target = event.target as HTMLAnchorElement;

    // Checks if the clicked element is an <a> tag pointing towards an ID
    if (!target || target.tagName !== 'A' || !target.hash) {
      return;
    }

    event.preventDefault();

    const logId = target.hash.substring(1);
    const targetElement = document.getElementById(logId);

    if (targetElement) {
      this.#logsScroll.scrollTop = targetElement.offsetTop - this.#logsScroll.offsetTop;
      targetElement.classList.add('logs__line--pointed');
      window.setTimeout(() => {
        targetElement.classList.remove('logs__line--pointed');
      }, 2000);
    }
  };
}
