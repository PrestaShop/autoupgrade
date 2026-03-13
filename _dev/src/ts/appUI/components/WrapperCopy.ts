/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DomLifecycle from '../../types/DomLifecycle';

export default class WrapperCopy implements DomLifecycle {
  readonly #elementClassToWatch = 'wrapper-copy__button';
  readonly #elementClassWhenCopied = 'wrapper-copy__button--copied';
  readonly #dataAttribute: string = 'targetId';
  public mount = () => {
    document.addEventListener('click', this.#onClick);
  };

  public beforeDestroy = () => {
    document.removeEventListener('click', this.#onClick);
  };

  readonly #onClick = async (ev: Event): Promise<void> => {
    const target = this.#getEventTargetIfCopyButton(ev);

    if (!target) {
      return;
    }

    target.classList.add(this.#elementClassWhenCopied);
    target.blur();
    setTimeout(() => {
      target.classList.remove(this.#elementClassWhenCopied);
    }, 1500);

    const contentsTarget = document.getElementById(this.#getTargetIdOfCopyButton(target));

    if (!contentsTarget) {
      throw new Error(
        `Target from ID #${this.#getTargetIdOfCopyButton(target)} cannot be found in the DOM.`
      );
    }
    await navigator.clipboard.writeText((contentsTarget as HTMLPreElement).innerText);
  };

  #getEventTargetIfCopyButton(ev: Event): HTMLElement | null {
    const target = ev.target ? (ev.target as HTMLElement) : null;

    if (!target) {
      return null;
    }

    if (target.classList.contains(this.#elementClassToWatch)) {
      return target;
    }

    return target.closest(`.${this.#elementClassToWatch}`);
  }

  #getTargetIdOfCopyButton(element: HTMLElement): string {
    if (!element.dataset[this.#dataAttribute]) {
      throw new Error(`Missing data ${this.#dataAttribute} from dataset.`);
    }

    return element.dataset[this.#dataAttribute]!;
  }
}
