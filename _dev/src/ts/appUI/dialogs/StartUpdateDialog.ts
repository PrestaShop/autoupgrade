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
import DialogAbstract from './DialogAbstract';

export default class StartUpdateDialog extends DialogAbstract {
  protected readonly formId = 'form-confirm-update';
  protected readonly confirmCheckboxId = 'dialog-start-update-own-backup';

  public mount = (): void => {
    this.form.addEventListener('submit', this.onSubmit);
    this.form.addEventListener('change', this.#onChange);

    this.#updateSubmitButtonStatus(
      document.getElementById('dialog-start-update-own-backup') as HTMLInputElement | undefined
    );
  };

  public beforeDestroy = (): void => {
    this.form.removeEventListener('submit', this.onSubmit);
    this.form.removeEventListener('change', this.#onChange);
  };

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    // We implement the same way to check from the other scripts, even though there is only one value.
    // This will ease any potential refacto.
    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  get #submitButton(): HTMLButtonElement {
    const submitButton = Array.from(this.form.elements).find(
      (element) => element instanceof HTMLButtonElement && element.type === 'submit'
    ) as HTMLButtonElement | null;

    if (!submitButton) {
      throw new Error(`No submit button found for form ${this.form.id}`);
    }

    return submitButton;
  }

  readonly #onChange = async (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    if (optionInput.id === this.confirmCheckboxId) {
      this.#updateSubmitButtonStatus(optionInput);
    }
  };

  #updateSubmitButtonStatus(input?: HTMLInputElement): void {
    if (!input || input.checked) {
      this.#submitButton.removeAttribute('disabled');
    } else {
      this.#submitButton.setAttribute('disabled', 'true');
    }
  }
}
