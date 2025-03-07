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
