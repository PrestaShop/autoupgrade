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
import StepPage from './StepPage';
import api from '../api/RequestHandler';

export default class RestorePageBackupSelection extends StepPage {
  protected stepCode = 'backup-selection';

  constructor() {
    super();
  }

  public mount = () => {
    this.initStepper();
    this.#form.addEventListener('change', this.#saveForm.bind(this));
    this.#form.addEventListener('submit', this.#handleSubmit);
  };

  public beforeDestroy = () => {
    this.#form.removeEventListener('change', this.#saveForm.bind(this));
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
