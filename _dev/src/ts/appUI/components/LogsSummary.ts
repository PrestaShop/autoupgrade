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
