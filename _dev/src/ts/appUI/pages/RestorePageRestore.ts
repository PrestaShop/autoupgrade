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
