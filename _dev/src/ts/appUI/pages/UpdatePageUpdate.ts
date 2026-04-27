/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';
import api from '../api/RequestHandler';
import ProcessContainer from '../components/ProcessContainer';

export default class UpdatePageUpdate extends StepPage {
  protected stepCode = 'update';
  #processContainer: ProcessContainer;
  #restoreAlertForm: null | HTMLFormElement = null;
  #restoreButtonForm: null | HTMLFormElement = null;
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

    this.#restoreAlertForm?.removeEventListener('submit', this.#handleSubmit);
    this.#restoreButtonForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitErrorReportForm?.removeEventListener('submit', this.#handleSubmit);
  };

  #onError = (): void => {
    this.#restoreAlertForm = document.forms.namedItem('restore-alert');
    this.#restoreAlertForm?.addEventListener('submit', this.#handleSubmit);

    this.#submitErrorReportForm = document.forms.namedItem('submit-error-report');
    this.#submitErrorReportForm?.addEventListener('submit', this.#handleSubmit);

    this.#restoreButtonForm = document.forms.namedItem('restore-button');
    this.#restoreButtonForm?.addEventListener('submit', this.#handleSubmit);
  };

  #handleSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToSubmit!);
  };
}
