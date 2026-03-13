/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';
import api from '../api/RequestHandler';

export default class RestorePageBackupSelection extends StepPage {
  protected stepCode = 'backup-selection';

  constructor() {
    super();
  }

  public mount = () => {
    this.initStepper();
    this.#form.addEventListener('change', this.#saveForm);
    this.#form.addEventListener('submit', this.#handleSubmit);
  };

  public beforeDestroy = () => {
    this.#form.removeEventListener('change', this.#saveForm);
    this.#form.removeEventListener('submit', this.#handleSubmit);
  };

  get #form(): HTMLFormElement {
    return document.forms.namedItem('backup_choice')!;
  }

  #saveForm = async () => {
    const routeToSave = this.#form.dataset.routeToSave;

    if (!routeToSave) {
      throw new Error('No route to save form provided. Impossible to save form.');
    }

    await this.#sendForm(routeToSave);
  };

  #handleSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    let routeToSubmit: string | undefined;

    if ((event.submitter as HTMLButtonElement)?.value === 'delete') {
      routeToSubmit = this.#form.dataset.routeToSubmitDelete;
    } else {
      routeToSubmit = this.#form.dataset.routeToSubmitRestore;
    }

    if (!routeToSubmit) {
      throw new Error('No route to submit form provided. Impossible to submit form.');
    }

    await this.#sendForm(routeToSubmit);
  };

  #sendForm = async (routeToSend: string) => {
    const formData = new FormData(this.#form);
    await api.post(routeToSend, formData);
  };
}
