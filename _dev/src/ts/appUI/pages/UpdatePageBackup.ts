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

    this.#processContainer.mount();
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
