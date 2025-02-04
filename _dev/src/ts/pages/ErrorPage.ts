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
import api from '../api/RequestHandler';
import ErrorPageBuilder from '../components/ErrorPageBuilder';
import { logStore } from '../store/LogStore';
import { ApiError } from '../types/apiTypes';
import { Severity } from '../types/logsTypes';
import Hydration from '../utils/Hydration';
import PageAbstract from './PageAbstract';

export default class ErrorPage extends PageAbstract {
  public static readonly templateId: string = 'error-page-template';

  isOnHomePage: boolean = false;

  constructor() {
    super();

    this.isOnHomePage = new URLSearchParams(window.location.search).get('route') === 'home-page';
  }

  public mount = (): void => {
    // If the error page is already present on the DOM (For instance on a whole page refresh),
    // initialize it at once instead of waiting for an event.
    const errorPageFromBackEnd = document.querySelector('.error-page');
    if (errorPageFromBackEnd) {
      this.#mountErrorPage(errorPageFromBackEnd);
    } else {
      this.#errorTemplateElement.addEventListener(
        Hydration.hydrationEventName,
        this.#onError.bind(this),
        { once: true }
      );
    }
  };

  public beforeDestroy = (): void => {
    this.#errorTemplateElement.removeEventListener(
      Hydration.hydrationEventName,
      this.#onError.bind(this)
    );
    this.#submitErrorReportForm?.removeEventListener('submit', this.#onSubmit);
    logStore.clearLogs();
  };

  get #errorTemplateElement(): HTMLTemplateElement {
    const element = document.getElementById(ErrorPage.templateId);

    if (!element) {
      throw new Error('Error template not found');
    }

    ['target'].forEach((data) => {
      if (!element.dataset[data]) {
        throw new Error(`Missing data ${data} from element dataset.`);
      }
    });

    return element as HTMLTemplateElement;
  }

  #createErrorPage(event: CustomEvent<ApiError>): void {
    // Duplicate the error template before alteration
    const errorElement = this.#errorTemplateElement.content.cloneNode(true) as DocumentFragment;

    const pageBuilder = new ErrorPageBuilder(errorElement);
    pageBuilder.updateId(event.detail.type);
    pageBuilder.updateLeftColumn(event.detail.code);
    pageBuilder.updateDescriptionBlock(event.detail);

    // Store the contents in the logs so it can be used in the error reporting modal
    if (event.detail.additionalContents) {
      logStore.addLog({
        severity: Severity.SUCCESS,
        height: 0,
        offsetTop: 0,
        message: event.detail.additionalContents
      });
    }

    // Finally, append the result on the page
    const targetElementToUpdate = document.getElementById(
      this.#errorTemplateElement.dataset.target!
    );
    if (!targetElementToUpdate) {
      throw new Error('Target element cannot be found');
    }
    targetElementToUpdate.replaceChildren(errorElement);

    // Retrieve the route we called to fill in the context.
    let route: string | null = null;
    if (event.detail.requestParams?.responseURL) {
      const params = new URLSearchParams(new URL(event.detail.requestParams?.responseURL)?.search);
      route = params?.get('route');
    }

    logStore.addLog({
      severity: Severity.ERROR,
      height: 0,
      offsetTop: 0,
      message: `HTTP request failed: Route ${route ?? 'N/A'} - Type: ${event.detail.type ?? 'N/A'} - Code ${event.detail.code ?? 'N/A'}`
    });

    // Enable events and page features
    this.#mountErrorPage(document.querySelector('.error-page')!);
  }

  #mountErrorPage(errorPage: Element): void {
    this.#form.addEventListener('submit', this.#onSubmit, { once: true });

    this.#submitErrorReportForm?.addEventListener('submit', this.#onSubmit);

    // Display the proper action buttons
    const activeButtonElement = this.isOnHomePage
      ? errorPage.querySelector('#exit-button')
      : errorPage.querySelector('#home-page-form');

    if (activeButtonElement) {
      activeButtonElement.classList.remove('hidden');
    }
  }

  get #form(): HTMLFormElement {
    const form = document.forms.namedItem('home-page-form');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  get #submitErrorReportForm(): HTMLFormElement | null {
    return document.forms.namedItem('submit-error-report');
  }

  readonly #onError = async (event: Event | CustomEvent<ApiError>): Promise<void> => {
    if (!(event instanceof CustomEvent)) {
      console.debug('Unexpected type of event received.');
      return;
    }
    this.#createErrorPage(event);
  };

  readonly #onSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    await api.post(
      (event.target as HTMLFormElement).dataset.routeToSubmit!,
      new FormData(this.#form)
    );
  };
}
