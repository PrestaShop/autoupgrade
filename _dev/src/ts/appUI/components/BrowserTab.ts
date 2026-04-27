/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
    this.#setProcessEnd(window.AutoUpgradeVariables.translations.success);
  };

  public setError = () => {
    this.#setProcessEnd(window.AutoUpgradeVariables.translations.failed);
  };

  #setProcessEnd = (processEnd: string) => {
    this.#processEnd = processEnd;
    if (this.#documentHidden) {
      this.#updateProgressTitle();
    } else {
      this.beforeDestroy();
    }
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
