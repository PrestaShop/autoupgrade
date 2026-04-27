/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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

    this.#form.addEventListener('change', this.#saveForm);
    this.#form.addEventListener('submit', this.#handleSubmit);
    this.#form.addEventListener('click', this.#onFormClick);

    this.#form.dispatchEvent(new Event('change'));

    this.#onlineCardParent?.addEventListener(Hydration.hydrationEventName, this.#handleHydrate);
    this.#onlineRecommendedCardParent?.addEventListener(
      Hydration.hydrationEventName,
      this.#handleHydrate
    );
    this.#localCardParent?.addEventListener(Hydration.hydrationEventName, this.#handleHydrate);

    this.#toggleNextButton();
  };

  public beforeDestroy = () => {
    if (!this.#form) return;
    this.#form.removeEventListener('change', this.#saveForm);
    this.#form.removeEventListener('submit', this.#handleSubmit);
    this.#form.removeEventListener('click', this.#onFormClick);

    this.#onlineCardParent?.removeEventListener(Hydration.hydrationEventName, this.#handleHydrate);
    this.#onlineRecommendedCardParent?.removeEventListener(
      Hydration.hydrationEventName,
      this.#handleHydrate
    );
    this.#localCardParent?.removeEventListener(Hydration.hydrationEventName, this.#handleHydrate);
  };

  #sendForm = async (routeToSend: string) => {
    const formData = new FormData(this.#form!);
    await api.post(routeToSend, formData);
  };

  readonly #onFormClick = (event: MouseEvent) => {
    const target = event.target as HTMLElement;

    // Delegate links with additional contents to display
    if (target.tagName === 'A' && (target as HTMLAnchorElement).hash) {
      this.#onClickDialogLink(event);
      return;
    }

    // Delegate "Check requirements again" button
    const button = target.closest('button[data-action="check-requirements-again"]');
    if (button) {
      this.#saveForm();
    }
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
    this.#onlineInputElement?.classList.remove(this.radioLoadingClass);
    this.#onlineRecommendedInputElement?.classList.remove(this.radioLoadingClass);

    const routeToSave = this.#form!.dataset.routeToSave;

    if (!routeToSave) {
      throw new Error('No route to save form provided. Impossible to save form.');
    }

    let currentInputCheck = null;

    if (this.#onlineInputIsChecked) {
      currentInputCheck = this.#onlineInputElement!;
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
    if (this.#onlineInputIsChecked) {
      return this.#onlineInputElement!.dataset.requirementsAreOk === '1';
    }
    if (this.#recommendedOnlineInputIsChecked) {
      return this.#onlineRecommendedInputElement!.dataset.requirementsAreOk === '1';
    }
    if (this.#localInputIsCheckAndFullFilled) {
      return this.#localInputElement!.dataset.requirementsAreOk === '1';
    }
    return false;
  }

  // online option
  get #onlineCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_online') as HTMLDivElement | undefined;
  }

  get #onlineRecommendedCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_online_recommended') as HTMLDivElement | undefined;
  }

  get #onlineInputElement(): HTMLInputElement | undefined {
    return this.#form?.elements.namedItem('online') as HTMLInputElement | undefined;
  }

  get #onlineRecommendedInputElement(): HTMLInputElement | undefined {
    return this.#form?.elements.namedItem('online_recommended') as HTMLInputElement | undefined;
  }

  get #onlineInputIsChecked(): boolean {
    return (this.#onlineInputElement && this.#onlineInputElement.checked) || false;
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
