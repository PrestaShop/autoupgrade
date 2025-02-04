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
import ComponentAbstract from './ComponentAbstract';
import { LogEntry, Log, Severity, VisibleLogs } from '../types/logsTypes';
import { parseLogWithSeverity, debounce } from '../utils/logsUtils';
import DomLifecycle from '../types/DomLifecycle';
import { logStore } from '../store/LogStore';
import api from '../api/RequestHandler';
import { ApiError } from '../types/apiTypes';
import { isHttpErrorCode } from '../api/axiosError';
import ErrorPage from '../pages/ErrorPage';

// TODO: clear the debounce on beforeDestroy
export default class LogsViewer extends ComponentAbstract implements DomLifecycle {
  #logsIndexOffsets: Map<number, number> = new Map();
  #logsListHeight: number = this.#logsList.clientHeight;
  #isSummaryDisplayed: boolean = false;

  // -- virtual scroll configuration --
  private static CONFIG = {
    BUFFER_SIZE: 6, // The multiplier for the viewport height used to define the buffer zone for virtual scrolling.
    DEBOUNCE_TIME: 50, // The delay time (in ms) for debouncing the `refreshView` method.
    LOG_BEFORE_SCROLL: 120 // The number of logs to process before automatically scrolling to the bottom.
  };

  #formId = 'form-logs-download-button';

  #templateLogLine = this.queryElement<HTMLTemplateElement>(
    '#log-line',
    'Template log line not found'
  );

  #logsSummary = this.queryElement<HTMLDivElement>(
    '[data-slot-component="summary"]',
    'Logs summary not found'
  );

  #templateSummary = this.queryElement<HTMLTemplateElement>(
    '#log-summary',
    'Template summary not found'
  );

  #logsScroll = this.queryElement<HTMLDivElement>(
    '[data-slot-component="scroll"]',
    'Logs scroll not found'
  );

  #additionalContentsPanel = this.queryElement<HTMLPreElement>(
    '#log-additional-contents',
    'Panel of additional contents not found'
  );

  get #logsList() {
    return this.queryElement<HTMLDivElement>('[data-slot-component="list"]', 'Logs list not found');
  }

  public mount = () => {
    this.#logsScroll.addEventListener('scroll', this.#debouncedRefreshView.debounced);

    // delay needed because of side menu toggle on small screens
    // we set width to prevent the modification of the height of
    // the logs which would require too many resources to recalculate everything
    setTimeout(() => {
      this.#logsList.style.width = `${this.#logsList.offsetWidth}px`;
    }, 1000);
  };

  public beforeDestroy = () => {
    logStore.clearLogs();
    this.#logsScroll.removeEventListener('scroll', this.#debouncedRefreshView.debounced);
    this.#logsSummary.removeEventListener('click', this.#handleLinkEvent);
    this.#debouncedRefreshView.cancel();
  };

  /**
   * @public
   * @param {string[]} logs - Array of log strings to be parsed and displayed.
   * @returns {void}
   * @description Adds multiple logs to the logs list. Ensures each log is parsed, stored,
   * and added to the DOM. Automatically scrolls to the bottom after a certain number of logs
   * are added or when all logs are processed.
   * If the logs summary is currently displayed, the method exits early with a warning
   * (no logs can be added if the summary is shown).
   */
  public addLogs = (logs: string[]): void => {
    if (this.#isSummaryDisplayed) {
      console.warn('Cannot add logs because summary is displayed');
      return;
    }

    let count = 0;

    logs.forEach((log) => {
      this.#addLogToStore(log);
      count += 1;

      if (count > LogsViewer.CONFIG.LOG_BEFORE_SCROLL) {
        this.#scrollToBottom();
        count = 0;
      }
    });

    this.#scrollToBottom();
  };

  public addError = (error: ApiError): void => {
    const detailedError = document.getElementById(ErrorPage.templateId)?.content?.querySelector(`.error-page__desc .error-page__desc-${isHttpErrorCode(error.code) ? error.code : error.type}`);
    if (detailedError) {
      this.addLogs([`ERROR - ${detailedError.textContent}`]);
    }
    this.addLogs([`ERROR - HTTP request failed: Type: ${error.type || 'N/A'} - HTTP Code ${error.code || 'N/A'}`]);

    // Contents is added on the DOM in a hidden panel in order to:
    // - Display it if requested in the future
    // - Have a place to store it for the error report without displaying random contents in the logs
    if (error.additionalContents) {
      this.#additionalContentsPanel.innerText = error.additionalContents;
    }
  }

  /**
   * @private
   * @param {string} log - A single log string to be parsed, stored, and added to the DOM.
   * @returns {number} - The unique ID of the newly added log in the `logStore`.
   * @description Parses a log string to create a structured log entry. Adds the parsed log
   * to the `logStore`, updates the virtual scrolling infrastructure (offsets and height),
   * and appends the corresponding DOM element to the logs container.
   */
  #addLogToStore(log: string): number {
    const id = logStore.getLogsLength();
    const logEntry = parseLogWithSeverity(log);
    const HTMLElement = this.#createLogLine(logEntry);

    this.#logsList.appendChild(HTMLElement);

    const height = HTMLElement.offsetHeight;
    const offsetTop = HTMLElement.offsetTop;

    logStore.addLog({
      ...logEntry,
      height,
      offsetTop,
      HTMLElement
    });

    this.#logsIndexOffsets.set(id, offsetTop);
    this.#logsListHeight += height;

    return id;
  }

  /**
   * @private
   * @param {LogEntry | Log} log - A structured log entry containing severity and message.
   * @param {boolean} isSummary - Flag to indicate if it's for a summary log.
   * @returns {HTMLDivElement} - The DOM element representing the log line.
   * @description Generates an HTML log line (for regular logs or summary logs).
   */
  #createLogLine = (log: LogEntry | Log, isSummary: boolean = false): HTMLDivElement => {
    const logLineFragment = this.#templateLogLine.content.cloneNode(true) as DocumentFragment;
    const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;

    logLine.classList.add(`logs__line--${log.severity}`);
    logLine.setAttribute('data-status', log.severity);
    if (isSummary && 'offsetTop' in log) {
      const logLineContent = logLine.querySelector('.logs__line-content') as HTMLDivElement;
      logLineContent.textContent = log.message;

      const linkElement = this.#createSummaryLinkElement(log.severity);
      const linkClone = linkElement.cloneNode(true) as HTMLAnchorElement;
      linkClone.href = `#${String(log.offsetTop)}`;

      logLine.appendChild(linkClone);
    } else {
      logLine.textContent = log.message;
    }

    return logLine;
  };

  /**
   * @private
   * @description Scrolls the logs container to the bottom and triggers a visual refresh of the logs view.
   */
  #scrollToBottom = () => {
    this.#logsScroll.scrollTop = this.#logsListHeight;
    this.#refreshView();
  };

  /**
   * @private
   * @description Refreshes the view using the virtual scrolling technique.
   */
  #refreshView = () => {
    const { marginTop, marginBottom, visibleLogs } = this.#calculateVisibleLogs(
      this.#logsScroll.scrollTop,
      this.#logsScroll.clientHeight
    );

    this.#logsList.style.marginTop = `${marginTop}px`;
    this.#logsList.style.marginBottom = `${marginBottom}px`;

    this.#logsList.innerHTML = '';
    visibleLogs.forEach((log) => {
      if (log.HTMLElement) {
        this.#logsList.appendChild(log.HTMLElement);
      }
    });
  };

  /**
   * Calculates the visible margins (top and bottom) and returns visible logs.
   * @private
   * @param {number} scrollTop - Current scroll position (top).
   * @param {number} logsViewportHeight - Current viewport height.
   * @returns {VisibleLogs} - Margins and visible logs.
   */
  #calculateVisibleLogs(scrollTop: number, logsViewportHeight: number): VisibleLogs {
    const startBoundary = scrollTop - LogsViewer.CONFIG.BUFFER_SIZE * logsViewportHeight;
    const endBoundary =
      scrollTop + logsViewportHeight + LogsViewer.CONFIG.BUFFER_SIZE * logsViewportHeight;

    let marginTop = 0;
    let marginBottom = 0;

    const visibleLogs: Log[] = [];
    for (const [id, offsetTop] of this.#logsIndexOffsets.entries()) {
      const log = logStore.getLogs()[id];
      const logHeight = log.height;

      if (offsetTop + logHeight < startBoundary) {
        marginTop += logHeight;
      } else if (offsetTop > endBoundary) {
        marginBottom += logHeight;
      } else {
        visibleLogs.push(log);
      }
    }

    return { marginTop, marginBottom, visibleLogs };
  }

  #debouncedRefreshView = debounce(() => {
    this.#refreshView();
  }, LogsViewer.CONFIG.DEBOUNCE_TIME);

  /**
   * @public
   * @description Displays a summary of logs, dividing it into warnings and errors.
   * Creates DOM elements dynamically and adds event listeners for navigation.
   * Prevents showing a summary if the logs list is empty.
   */
  public displaySummary = async (): Promise<void> => {
    if (logStore.getLogsLength() === 0) {
      console.warn('Cannot display summary because logs are empty');
      return;
    }

    const fragment = document.createDocumentFragment();

    const warnings = logStore.getWarnings();
    if (warnings.length > 0) {
      const warningsSummary = this.#createSummary(Severity.WARNING, warnings);
      fragment.appendChild(warningsSummary);
    }

    const errors = logStore.getErrors();
    if (errors.length > 0) {
      const errorsSummary = this.#createSummary(Severity.ERROR, errors);
      fragment.appendChild(errorsSummary);
    }

    if (fragment.hasChildNodes()) {
      this.#logsSummary.addEventListener('click', this.#handleLinkEvent);
    }

    this.#logsSummary.appendChild(fragment);

    const downloadlogsButtonForm = document.forms.namedItem(this.#formId);
    if (!downloadlogsButtonForm) {
      throw new Error('Form to request the button to download logs cannot be found');
    }
    await api.post(
      downloadlogsButtonForm.dataset.downloadLogsRoute!,
      new FormData(downloadlogsButtonForm)
    );

    this.#logsSummary.appendChild(fragment);
    this.#isSummaryDisplayed = true;

    this.#scrollToBottom();
  };

  /**
   * @private
   * @param {Severity} severity - The severity of logs to summarize (e.g., warning, error).
   * @param {Log[]} logs - An array of structured logs for the given severity.
   * @returns {HTMLDivElement} - The summary HTML element for the grouped logs.
   * @description Creates a summary element grouping logs by severity.
   * Each log in the summary includes a link to its corresponding log line.
   */
  #createSummary(severity: Severity, logs: Log[]): HTMLDivElement {
    const summaryFragment = this.#templateSummary.content.cloneNode(true) as DocumentFragment;

    const summary = summaryFragment.querySelector('.logs__summary') as HTMLDivElement;
    summary.setAttribute('data-summary-severity', severity);

    const summaryScroll = summaryFragment.querySelector('.logs__summary-scroll') as HTMLDivElement;

    const title = this.#getSummaryTitle(severity);
    const titleContainer = summary.querySelector('[data-slot-template="title"]') as HTMLDivElement;
    titleContainer.textContent = title;

    const countContainer = summary.querySelector('[data-slot-template="count"]') as HTMLDivElement;
    countContainer.textContent = String(logs.length);

    logs.forEach((log) => {
      const logElement = this.#createLogLine(log, true);
      summaryScroll.appendChild(logElement);
    });

    return summary;
  }

  /**
   * @private
   * @param {Severity} severity - The severity type (e.g., WARNING, ERROR).
   * @returns {string} - The content of the title template.
   * @description Retrieves the title template for the given severity type and extracts its content.
   */
  #getSummaryTitle(severity: Severity): string {
    const titleTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-title`,
      `Summary ${severity} title not found`
    );

    const title = titleTemplate.content.cloneNode(true) as HTMLElement;

    return title.textContent!;
  }

  /**
   * @private
   * @param {Severity} severity - The severity type (e.g., WARNING, ERROR).
   * @returns {HTMLAnchorElement} - The created link element.
   * @description Creates a link element from the template corresponding to the given severity type.
   */
  #createSummaryLinkElement(severity: Severity): HTMLAnchorElement {
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

    const offsetTopToScroll = target.hash.substring(1);
    const logKey = [...this.#logsIndexOffsets.keys()].find(
      (key) => this.#logsIndexOffsets.get(key) === Number(offsetTopToScroll)
    );

    if (logKey === undefined) {
      return;
    }

    const HTMLElement = logStore.getLog(logKey).HTMLElement;

    if (HTMLElement === undefined) {
      return;
    }

    this.#logsScroll.scrollTop = Number(offsetTopToScroll);
    window.setTimeout(() => {
      HTMLElement.classList.add('logs__line--pointed');
    }, 100);
    window.setTimeout(() => {
      HTMLElement.classList.remove('logs__line--pointed');
    }, 2000);
  };
}
