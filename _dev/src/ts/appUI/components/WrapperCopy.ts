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
