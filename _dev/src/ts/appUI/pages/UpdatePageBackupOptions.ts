/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import api from '../api/RequestHandler';
import StepPage from './StepPage';

export default class UpdatePageBackupOptions extends StepPage {
  protected stepCode = 'backup';

  public mount(): void {
    this.initStepper();
    this.#updateForm.addEventListener('submit', this.#onFormSubmit);
    this.#backupForm.addEventListener('submit', this.#onFormSubmit);
    this.#backupForm.addEventListener('change', this.#onInputChange);
  }

  public beforeDestroy(): void {
    this.#updateForm.removeEventListener('submit', this.#onFormSubmit);
    this.#backupForm.removeEventListener('submit', this.#onFormSubmit);
    this.#backupForm.removeEventListener('change', this.#onInputChange);
  }

  get #backupForm(): HTMLFormElement {
    const form = document.forms.namedItem('update-backup-page-form');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSave', 'routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  get #updateForm(): HTMLFormElement {
    const form = document.forms.namedItem('update-backup-page-update-form');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  readonly #onInputChange = async (ev: Event): Promise<void> => {
    const optionInput = ev.target as HTMLInputElement;

    const data = new FormData(this.#backupForm);
    optionInput.setAttribute('disabled', 'true');
    await api.post(this.#backupForm.dataset.routeToSave!, data);
    optionInput.removeAttribute('disabled');
  };

  readonly #onFormSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToSubmit!, new FormData(form));
  };
}
