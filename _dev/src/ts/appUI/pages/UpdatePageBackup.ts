/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';
import api from '../api/RequestHandler';
import ProcessContainer from '../components/ProcessContainer';

export default class UpdatePageBackup extends StepPage {
  protected stepCode = 'backup';
  #processContainer: ProcessContainer;
  #submitSkipBackupForm: null | HTMLFormElement = null;
  #submitErrorReportForm: null | HTMLFormElement = null;
  #submitRetryForm: null | HTMLFormElement = null;
  #submitRetryAlert: null | HTMLFormElement = null;

  constructor() {
    super();

    const stepContent = document.getElementById('ua_step_content')!;
    const initialAction = stepContent.dataset.initialProcessAction!;

    this.#processContainer = new ProcessContainer(initialAction, {
      onError: this.#onError
    });
  }

  public mount = async () => {
    this.initStepper();
    await this.#processContainer.mount();
  };

  public beforeDestroy = (): void => {
    this.#processContainer.beforeDestroy();

    this.#submitSkipBackupForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitErrorReportForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitRetryForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitRetryAlert?.removeEventListener('submit', this.#handleSubmit);
  };

  #onError = (): void => {
    this.#submitSkipBackupForm = document.forms.namedItem('submit-skip-backup');
    this.#submitSkipBackupForm?.addEventListener('submit', this.#handleSubmit);

    this.#submitErrorReportForm = document.forms.namedItem('submit-error-report');
    this.#submitErrorReportForm?.addEventListener('submit', this.#handleSubmit);

    this.#submitRetryAlert = document.forms.namedItem('retry-alert');
    this.#submitRetryAlert?.addEventListener('submit', this.#handleSubmit);

    this.#submitRetryForm = document.forms.namedItem('retry-button');
    this.#submitRetryForm?.addEventListener('submit', this.#handleSubmit);
  };

  #handleSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToSubmit!);
  };
}
