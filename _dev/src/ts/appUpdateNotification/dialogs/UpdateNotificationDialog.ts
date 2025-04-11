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
import api from '../api/baseApi';
import DomLifecycle from '../../types/DomLifecycle';
import Analytics from '../../api/SegmentApi';

const analytics = new Analytics();

export default class UpdateNotificationDialog implements DomLifecycle {
  readonly #dialogId = 'dialog-update-notification';
  readonly #formRemindMeLaterId = 'remind-me-later-update';
  readonly #formSubmitId = 'submit-update';

  public mount(): void {
    if (!this.#dialog) {
      return;
    }

    analytics.track('[SUE] Update modal displayed');

    this.#dialog.showModal();

    this.#dialog.addEventListener('close', this.#preventClose);
    this.#remindMeLaterForm.addEventListener('submit', this.#sendForm);
    this.#submitForm.addEventListener('submit', this.#sendForm);
  }

  public beforeDestroy(): void {
    if (!this.#dialog) {
      return;
    }

    this.#dialog.removeEventListener('close', this.#preventClose);
    this.#remindMeLaterForm.removeEventListener('submit', this.#sendForm);
    this.#submitForm.removeEventListener('submit', this.#sendForm);
  }

  get #dialog(): HTMLDialogElement | null {
    const element = document.getElementById(this.#dialogId);
    return element instanceof HTMLDialogElement ? element : null;
  }

  #getFormById(formId: string, requireDataset: string[] = ['action']): HTMLFormElement {
    const form = document.forms.namedItem(formId);

    if (!form) {
      throw new Error('Form not found');
    }

    requireDataset.forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  get #remindMeLaterForm(): HTMLFormElement {
    return this.#getFormById(this.#formRemindMeLaterId);
  }

  get #submitForm(): HTMLFormElement {
    return this.#getFormById(this.#formSubmitId);
  }

  #sendForm = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;
    const submitButton = event.submitter as HTMLButtonElement;

    if (submitButton.dataset.dismiss === 'dialog') {
      this.#dialog!.close();
    }

    try {
      const params = { action: form.dataset.action };

      if (submitButton.value) {
        Object.assign(params, { value: submitButton.value });
        await analytics.track('[SUE] Update modal snoozed', {
          representation_delay: submitButton.value
        });
      } else {
        await analytics.track('[SUE] Update module opened following modal display');
      }

      const response = await api.post('', null, {
        params: params
      });

      if (response.data.url_to_redirect) {
        window.location = response.data.url_to_redirect;
      }

      this.beforeDestroy();
    } catch (error) {
      console.error(error);
    }
  };

  #preventClose = (event: Event): void => {
    event.preventDefault();
  };
}
