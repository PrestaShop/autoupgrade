/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import ComponentAbstract from './ComponentAbstract';
import { Destroyable } from '../../types/DomLifecycle';

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
