/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import ComponentAbstract from './ComponentAbstract';
import { Destroyable } from '../../types/DomLifecycle';

export default class LogsSummary extends ComponentAbstract implements Destroyable {
  #logsSummaryText = this.queryElement<HTMLDivElement>(
    '[data-slot-component="text"]',
    'Logs summary text not found'
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
   * @param text - text summary to display.
   * @description Allows to update the summary text of the logs.
   */
  public setLogsSummaryText = (text: string): void => {
    this.#logsSummaryText.innerText = text;
  };
}
