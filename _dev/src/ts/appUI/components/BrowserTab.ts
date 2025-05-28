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
import DomLifecycle from '../../types/DomLifecycle';

export default class BrowserTab implements DomLifecycle {
  readonly #defaultTitle: string = document.title;
  #documentHidden: boolean = false;
  #percentProgress: number = 0;
  #processEnd: string | null = null;

  public mount = () => {
    document.addEventListener('visibilitychange', this.#onVisibilityChange);
  };

  public beforeDestroy = () => {
    this.#setDefaultTitle();
    document.removeEventListener('visibilitychange', this.#onVisibilityChange);
  };

  public updatePercentProgress = (state: number) => {
    this.#percentProgress = state;
    if (this.#documentHidden) {
      this.#updateProgressTitle();
    }
  };

  public setSuccess = () => {
    this.#setProcessEnd('SUCCESS');
  };

  public setError = () => {
    this.#setProcessEnd('FAILED');
  };

  #setProcessEnd = (processEnd: string) => {
    this.#processEnd = processEnd;
    this.#updateProgressTitle();
  };

  #onVisibilityChange = () => {
    this.#documentHidden = document.hidden;
    if (!this.#documentHidden) {
      this.#setDefaultTitle();
      if (this.#processEnd) {
        this.beforeDestroy();
      }
      return;
    }
    this.#updateProgressTitle();
  };

  #setDefaultTitle = () => {
    document.title = this.#defaultTitle;
  };

  #updateProgressTitle = () => {
    const prependTitle = this.#processEnd ?? `${this.#percentProgress}%`;
    document.title = `${prependTitle} - ${this.#defaultTitle}`;
  };
}
