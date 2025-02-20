import api from '../api/baseApi';
import DomLifecycle from '../../types/DomLifecycle';

export default class UpdateNotificationDialog implements DomLifecycle {
  readonly #dialogId = 'dialog-update-notification';
  readonly #formDismissId = 'dismiss-update';
  readonly #formSubmitId = 'submit-update';

  public mount() {
    this.#dialog.showModal();

    this.#dialog.addEventListener('close', this.#preventClose);
    this.#dismissForm.addEventListener('submit', this.#sendForm);
    this.#submitForm.addEventListener('submit', this.#sendForm);
  }

  public beforeDestroy(): void {
    this.#dialog.removeEventListener('close', this.#preventClose);
    this.#dismissForm.removeEventListener('submit', this.#sendForm);
    this.#submitForm.removeEventListener('submit', this.#sendForm);
  }

  get #dialog(): HTMLDialogElement {
    const dialog = document.getElementById(this.#dialogId);
    if (!dialog || !(dialog instanceof HTMLDialogElement)) {
      throw new Error('Dialog not found');
    }

    return dialog;
  }

  get #dismissForm(): HTMLFormElement {
    const form = document.forms.namedItem(this.#formDismissId);
    if (!form) {
      throw new Error('Form not found');
    }

    ['action'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  get #submitForm(): HTMLFormElement {
    const form = document.forms.namedItem(this.#formSubmitId);
    if (!form) {
      throw new Error('Form not found');
    }

    ['action'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  #sendForm = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;
    const submitButton = event.submitter as HTMLElement;

    if (submitButton.dataset.dismiss === 'dialog') {
      this.#dialog.close();
    }

    const response = await api.post('', null, {
      params: { action: form.dataset.action }
    });

    if (response.data.url_to_redirect) {
      window.location = response.data.url_to_redirect;
    }

    this.beforeDestroy();
  };

  #preventClose = (event: Event): void => {
    event.preventDefault();
  };
}
