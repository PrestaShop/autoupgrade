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
import { ApiError } from '../types/apiTypes';
import Hydration from '../utils/Hydration';
import PageAbstract from './PageAbstract';

export default class ErrorPage extends PageAbstract {
  public static templateId: string = 'error-page-template';
  // TODO: Improve this by putting the target in the template and sent it from the back end
  public static targetElementIdToUpdate: string = 'ua_page';

  isOnHomePage: boolean = false;

  constructor() {
    super();

    this.isOnHomePage = new URLSearchParams(window.location.search).get('route') === 'home-page';
  }

  public mount = (): void => {
    
    // If the error page is already present on the DOM (For instance on a whole page refresh),
    // initalize it at once instead of waiting for an event.
    const errorPageFromBackEnd = document.querySelector('.error-page');
    if (errorPageFromBackEnd) {
      this.#mountErrorPage(errorPageFromBackEnd);
    } else {
      this.#errorTemplateElement.addEventListener(Hydration.hydrationEventName, this.#onError.bind(this), {once: true});
    }
  };

  public beforeDestroy = (): void => {
    this.#errorTemplateElement.removeEventListener(Hydration.hydrationEventName, this.#onError.bind(this));
  };

  get #errorTemplateElement(): HTMLTemplateElement {
    const element = document.getElementById(ErrorPage.templateId);

    if (!element) {
      throw new Error('Error template not found');
    }

    return element as HTMLTemplateElement;
  }

  #onError = async (event: CustomEvent<ApiError>): Promise<void> => {
    this.#createErrorPage(event);
  }

  #createErrorPage(event: CustomEvent<ApiError>): void {
    // Duplicate the error template before alteration
    const errorElement = this.#errorTemplateElement.content.cloneNode(true) as DocumentFragment;

    // Set the id of the cloned element
    const errorChild = errorElement.getElementById('ua_error_placeholder');
    if (errorChild) {
      errorChild.id = `ua_error_${event.detail.type}`;
    }

    // If code is a HTTP error number (i.e 404, 500 etc.), let's change the text in the left column with it.
    if (typeof event.detail.code === 'number' && event.detail.code >= 300 && event.detail.code.toString().length === 3) {
      const strigifiedCode = event.detail.code.toString().replaceAll('0', 'O');
      const errorCodeSlotElements = errorElement.querySelectorAll('.error-page__code-char');
      errorCodeSlotElements.forEach((element: Element, index: number) => {
        element.innerHTML = strigifiedCode[index];
      });
      errorElement.querySelector('.error-page__code-missing')?.classList.add('hidden');
    }

    // Display a user friendly text related to the code if it exists, otherwise write the error code.
    const errorDescriptionElement = errorElement.querySelector('.error-page__desc');
    const userFriendlyDescriptionElement = errorDescriptionElement?.querySelector(`.error-page__desc-${event.detail.code || event.detail.type}`);
    if (userFriendlyDescriptionElement) {
      userFriendlyDescriptionElement.classList.remove('hidden');
    } else if (errorDescriptionElement && event.detail.type) {
      errorDescriptionElement.innerHTML = event.detail.type;
    }

    // Store the contents in the hidden field so it can be used in the error reporting modal
    const additionalContentsElement = errorElement.querySelector('.error-page__contents');
    if (additionalContentsElement && event.detail.additionalContents) {
      additionalContentsElement.innerHTML = new String(event.detail.additionalContents).toString();
    }

    // Finally, append the result on the page
    const targetElementToUpdate = document.getElementById(ErrorPage.targetElementIdToUpdate);
    if (!targetElementToUpdate) {
      throw new Error('Target element cannot be found');
    }
    targetElementToUpdate.replaceChildren(errorElement);

    // Enable events and page features
    this.#mountErrorPage(document.querySelector('.error-page')!);
  }

  #mountErrorPage(errorPage: Element): void {
    console.log('mounting', errorPage);
    this.#form.addEventListener('submit', this.#onSubmit, {once: true});

    // Display the proper action buttons
    const activeButtonElement = this.isOnHomePage
      ? errorPage.querySelector('.error-page__exit-button')
      : errorPage.querySelector('.error-page__home-page-form');

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

  readonly #onSubmit = async (event: Event): Promise<void> => {
    event.preventDefault();

    await api.post(this.#form.dataset.routeToSubmit!, new FormData(this.#form));
  };
}
