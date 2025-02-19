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
import ProgressBar from './ProgressBar';
import LogsSummary from './LogsSummary';
import LogsViewer from './LogsViewer';
import { ApiResponseAction } from '../types/apiTypes';
import DomLifecycle from '../types/DomLifecycle';

export default class ProgressTracker extends ComponentAbstract implements DomLifecycle {
  #logsSummary: LogsSummary | null = new LogsSummary(this.#logsSummaryContainer);
  #progressBar: ProgressBar | null = new ProgressBar(this.#progressBarContainer);
  #logsViewer: NonNullable<LogsViewer> = new LogsViewer(this.#logsViewerContainer);

  public mount = () => {
    this.#logsViewer.mount();
  };

  public beforeDestroy = () => {
    this.#logsViewer.beforeDestroy();
  };

  get #logsSummaryContainer() {
    return this.queryElement<HTMLDivElement>(
      '[data-component="logs-summary"]',
      'Logs summary not found'
    );
  }

  get #progressBarContainer() {
    return this.queryElement<HTMLDivElement>(
      '[data-component="progress-bar"]',
      'Progress bar not found'
    );
  }

  get #logsViewerContainer() {
    return this.queryElement<HTMLDivElement>(
      '[data-component="logs-viewer"]',
      'Logs viewer not found'
    );
  }

  /**
   * @public
   * @param {ApiResponseAction} data - API response data containing the progress information,
   * next description, and log details.
   * @returns {void}
   * @description Updates the progress tracker components:
   * - Updates the log summary with the next description.
   * - Updates the progress bar with the progress percentage.
   * - Adds new logs to the logs viewer.
   * - Adds errors if present to logs viewer.
   */
  public updateProgress = (data: ApiResponseAction): void => {
    this.#logsSummary?.setLogsSummaryText(data.next_desc ?? '');
    this.#progressBar?.setProgressPercentage(data.nextParams?.progressPercentage || 0);
    this.#logsViewer.addLogs(data.nextQuickInfo);
    if (data.apiError) {
      this.#logsViewer.addError(data.apiError);
    }
  };

  /**
   * @public
   * @returns {void}
   * @description Displays a summary of error logs in the logs viewer.
   *              Destroy and null logsSummary and ProgressBar.
   */
  public endProgress = (): void => {
    this.#logsSummary?.beforeDestroy();
    this.#logsSummary = null;

    this.#progressBar?.beforeDestroy();
    this.#progressBar = null;

    this.#logsViewer.displaySummary();
  };
}
