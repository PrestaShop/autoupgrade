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
import { analytics } from '../main';
import Hydration from '../utils/Hydration';

export default class UpdatePageVersionChoice extends StepPage {
  protected stepCode = 'version-choice';
  private radioLoadingClass = 'radio--show-requirements-loader';

  constructor() {
    super();
  }

  public mount = () => {
    this.initStepper();
    if (!this.#form) return;

    this.#form.addEventListener('change', this.#saveForm.bind(this));
    this.#form.addEventListener('submit', this.#handleSubmit);

    this.#form.dispatchEvent(new Event('change'));

    this.#onlineMaxCardParent?.addEventListener(Hydration.hydrationEventName, this.#handleHydrate);
    this.#onlineRecommendedCardParent?.addEventListener(
      Hydration.hydrationEventName,
      this.#handleHydrate
    );
    this.#localCardParent?.addEventListener(Hydration.hydrationEventName, this.#handleHydrate);

    this.#toggleNextButton();
    this.#addListenerToCheckRequirementsAgainButtons();
  };

  public beforeDestroy = () => {
    if (!this.#form) return;
    this.#form.removeEventListener('change', this.#saveForm);
    this.#form.removeEventListener('submit', this.#handleSubmit);
    this.#onlineMaxCardParent?.removeEventListener(
      Hydration.hydrationEventName,
      this.#toggleNextButton
    );
    this.#onlineRecommendedCardParent?.removeEventListener(
      Hydration.hydrationEventName,
      this.#toggleNextButton
    );
    this.#localCardParent?.removeEventListener(
      Hydration.hydrationEventName,
      this.#toggleNextButton
    );
    this.#checkRequirementsAgainButtons?.forEach((element) => {
      element.removeEventListener('click', this.#saveForm);
    });
    this.#requirementsListContainer?.removeEventListener('click', this.#onClickDialogLink);
  };

  #sendForm = async (routeToSend: string) => {
    const formData = new FormData(this.#form!);
    await api.post(routeToSend, formData);
  };

  #addListenerToCheckRequirementsAgainButtons = () => {
    if (this.#checkRequirementsAgainButtons?.length) {
      this.#checkRequirementsAgainButtons.forEach((element) => {
        element.addEventListener('click', this.#saveForm);
      });
    }
  };

  #addListenerToRequirementsLinks = () => {
    this.#requirementsListContainer?.addEventListener('click', this.#onClickDialogLink);
  };

  #onClickDialogLink = async (event: MouseEvent) => {
    const target = event.target as HTMLAnchorElement;

    // Checks if the clicked element is an <a> tag pointing towards an ID
    if (!target || target.tagName !== 'A' || !target.hash) {
      return;
    }

    event.preventDefault();

    const hashRoute = target.hash.substring(1);
    await api.post(hashRoute);
  };

  #handleHydrate = () => {
    this.#toggleNextButton();
    this.#addListenerToCheckRequirementsAgainButtons();
    this.#addListenerToRequirementsLinks();
  };

  #toggleNextButton = () => {
    if (this.#currentChannelRequirementsAreOk) {
      this.#submitButton?.removeAttribute('disabled');
    } else {
      this.#submitButton?.setAttribute('disabled', 'true');
    }
  };

  #saveForm = async () => {
    api.abortCurrentPost();

    this.#localInputElement?.classList.remove(this.radioLoadingClass);
    this.#onlineMaxInputElement?.classList.remove(this.radioLoadingClass);
    this.#onlineRecommendedInputElement?.classList.remove(this.radioLoadingClass);

    const routeToSave = this.#form!.dataset.routeToSave;

    if (!routeToSave) {
      throw new Error('No route to save form provided. Impossible to save form.');
    }

    let currentInputCheck = null;

    if (this.#onlineMaxInputIsChecked) {
      currentInputCheck = this.#onlineMaxInputElement!;
    }

    if (this.#recommendedOnlineInputIsChecked) {
      currentInputCheck = this.#onlineRecommendedInputElement!;
    }

    if (this.#localInputIsCheckAndFullFilled) {
      currentInputCheck = this.#localInputElement!;
    }

    if (currentInputCheck) {
      currentInputCheck.removeAttribute('data-requirements-are-ok');
      this.#toggleNextButton();
      currentInputCheck.classList.add(this.radioLoadingClass);
      await this.#sendForm(routeToSave);
    }
  };

  #handleSubmit = async (event: Event) => {
    event.preventDefault();
    const routeToSubmit = this.#form!.dataset.routeToSubmit;

    if (!routeToSubmit) {
      throw new Error('No route to submit form provided. Impossible to submit form.');
    }

    analytics.track('[SUE] Version choice submitted', {
      upgrade_channel: this.#localInputIsChecked ? 'local' : 'online'
    });

    await this.#sendForm(routeToSubmit);
  };

  // global form
  get #form(): HTMLFormElement | null {
    return document.forms.namedItem('version_choice');
  }

  get #submitButton(): HTMLButtonElement | undefined {
    return this.#form
      ? (Array.from(this.#form.elements).find(
          (element) => element instanceof HTMLButtonElement && element.type === 'submit'
        ) as HTMLButtonElement | undefined)
      : undefined;
  }

  get #currentChannelRequirementsAreOk(): boolean {
    if (this.#onlineMaxInputIsChecked) {
      return this.#onlineMaxInputElement!.dataset.requirementsAreOk === '1';
    }
    if (this.#recommendedOnlineInputIsChecked) {
      return this.#onlineRecommendedInputElement!.dataset.requirementsAreOk === '1';
    }
    if (this.#localInputIsCheckAndFullFilled) {
      return this.#localInputElement!.dataset.requirementsAreOk === '1';
    }
    return false;
  }

  get #requirementsListContainer(): HTMLDivElement | null | undefined {
    return this.#form?.querySelector('[data-slot-component="requirements"]');
  }

  get #checkRequirementsAgainButtons(): HTMLButtonElement[] | undefined {
    return this.#form
      ? (Array.from(this.#form.elements).filter(
          (element): element is HTMLButtonElement =>
            element instanceof HTMLButtonElement &&
            element.dataset.action === 'check-requirements-again'
        ) as HTMLButtonElement[])
      : undefined;
  }

  // online option
  get #onlineMaxCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_online_max') as HTMLDivElement | undefined;
  }

  get #onlineRecommendedCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_online_recommended') as HTMLDivElement | undefined;
  }

  get #onlineMaxInputElement(): HTMLInputElement | undefined {
    return this.#form?.elements.namedItem('online_max') as HTMLInputElement | undefined;
  }

  get #onlineRecommendedInputElement(): HTMLInputElement | undefined {
    return this.#form?.elements.namedItem('online_recommended') as HTMLInputElement | undefined;
  }

  get #onlineMaxInputIsChecked(): boolean {
    return (this.#onlineMaxInputElement && this.#onlineMaxInputElement.checked) || false;
  }

  get #recommendedOnlineInputIsChecked(): boolean {
    return (
      (this.#onlineRecommendedInputElement && this.#onlineRecommendedInputElement.checked) || false
    );
  }

  // local option
  get #localCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_archive') as HTMLDivElement | undefined;
  }

  get #localInputElement(): HTMLInputElement | undefined {
    return this.#form?.elements.namedItem('local') as HTMLInputElement | undefined;
  }

  get #localInputIsChecked(): boolean {
    return this.#localInputElement?.checked || false;
  }

  get #archiveZipSelectElement(): HTMLSelectElement | undefined {
    return this.#form?.elements.namedItem('archive_zip') as HTMLSelectElement | undefined;
  }

  get #archiveZipIsFilled(): boolean {
    return !!this.#archiveZipSelectElement?.value;
  }

  get #archiveXmlSelectElement(): HTMLSelectElement | undefined {
    return this.#form!.elements.namedItem('archive_xml') as HTMLSelectElement | undefined;
  }

  get #archiveXmlIsFilled(): boolean {
    return (this.#archiveXmlSelectElement && !!this.#archiveXmlSelectElement.value) || false;
  }

  get #localInputIsCheckAndFullFilled(): boolean {
    return this.#localInputIsChecked && this.#archiveZipIsFilled && this.#archiveXmlIsFilled;
  }
}
