/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';
import ProcessContainer from '../components/ProcessContainer';
import api from '../api/RequestHandler';

export default class RestorePageRestore extends StepPage {
  protected stepCode = 'restore';
  #processContainer: ProcessContainer;
  #tryAgainButtonForm: null | HTMLFormElement = null;
  #submitErrorReportForm: null | HTMLFormElement = null;

  constructor() {
    super();

    const stepContent = document.getElementById('ua_step_content')!;
    const initialAction = stepContent.dataset.initialProcessAction!;

    this.#processContainer = new ProcessContainer(initialAction, {
      onError: this.#onError
    });
  }

  public mount = (): void => {
    this.initStepper();
    this.#processContainer.mount();
  };

  public beforeDestroy = () => {
    this.#processContainer.beforeDestroy();

    this.#tryAgainButtonForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitErrorReportForm?.removeEventListener('submit', this.#handleSubmit);
  };

  #onError = (): void => {
    this.#tryAgainButtonForm = document.forms.namedItem('try-again-button');
    this.#tryAgainButtonForm?.addEventListener('submit', this.#handleSubmit);

    this.#submitErrorReportForm = document.forms.namedItem('submit-error-report');
    this.#submitErrorReportForm?.addEventListener('submit', this.#handleSubmit);
  };

  #handleSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;
    const routeToSubmit = form.dataset.routeToSubmit;

    if (!routeToSubmit) {
      throw new Error('No route to submit form provided. Impossible to submit form.');
    }

    await api.post(routeToSubmit);
  };
}
