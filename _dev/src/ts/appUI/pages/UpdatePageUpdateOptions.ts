/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';
import api from '../api/RequestHandler';
import { analytics } from '../main';

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

    const data = new FormData(this.#form);

    analytics.track('[SUE] Update options configured', {
      disable_all_overrides: !!data.get('disable_overrides'),
      disable_non_native_modules: !!data.get('disable_non_native_modules'),
      regenerate_customized_email_templates: !!data.get('regenerate_email_templates')
    });

    await api.post(this.#form.dataset.routeToSubmit!, data);
  };
}
