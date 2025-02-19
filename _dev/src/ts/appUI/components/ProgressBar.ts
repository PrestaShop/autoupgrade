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
import { Destroyable } from '../types/DomLifecycle';

export default class ProgressBar extends ComponentAbstract implements Destroyable {
  #progressBar = this.queryElement<HTMLDivElement>(
    '[role="progressbar"]',
    'Progress bar not found'
  );

  /**
   * @public
   * @description Removes the associated DOM element from the document.
   */
  public beforeDestroy = () => {
    this.element.remove();
  };

  /**
   * @public
   * @param percentage - A number between 0 and 100 representing the percentage of progress.
   * @description Update all progress bar attribute from percentage given.
   */
  public setProgressPercentage = (percentage: number) => {
    const progressPercentage = Number(percentage).toString();
    const progressBar = this.#progressBar;

    progressBar.style.width = `${progressPercentage}%`;
    progressBar.setAttribute('aria-valuenow', progressPercentage);

    const titleTemplate = progressBar.dataset.titleTemplate;
    if (titleTemplate) {
      const formattedTitle = this.#formatTitle(titleTemplate, progressPercentage);
      progressBar.title = formattedTitle;
      progressBar.setAttribute('aria-label', formattedTitle);
    } else {
      console.warn('Title template not found on progress bar');
    }
  };

  /**
   * @param {string} template - The title template containing "{progress_percentage}".
   * @param {string} percentage - The progress percentage as a string.
   * @returns string - The formatted title.
   * @description Replaces "{progress_percentage}" in the template with the given percentage.
   */
  #formatTitle(template: string, percentage: string): string {
    return template.replace('{progress_percentage}', percentage);
  }
}
