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

export default class UpdatePageUpdateOptions extends StepPage {
  protected stepCode = 'update-options';

  public mount() {
    this.initStepper();
    this.#form.addEventListener('submit', this.#onSubmit);
    this.#form.addEventListener('change', this.#onChange);
  }

  public beforeDestroy() {
    try {
      this.#form.removeEventListener('submit', this.#onSubmit);
      this.#form.removeEventListener('change', this.#onChange);
    } catch {
      // Do Nothing, page is likely removed from the DOM already
    }
  }

  get #form(): HTMLFormElement {
    const form = document.forms.namedItem('update-options-page-form');
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

  readonly #onChange = async (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    const data = new FormData(this.#form);
    optionInput.setAttribute('disabled', 'true');
    await api.post(this.#form.dataset.routeToSave!, data);
    optionInput.removeAttribute('disabled');
  };

  readonly #onSubmit = async (event: Event) => {
    event.preventDefault();

    await api.post(this.#form.dataset.routeToSubmit!, new FormData(this.#form));
  };
}
