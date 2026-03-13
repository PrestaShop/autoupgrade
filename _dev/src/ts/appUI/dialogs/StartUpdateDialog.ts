/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
