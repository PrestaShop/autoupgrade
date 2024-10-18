import UpdatePage from './UpdatePage';
import api from '../api/RequestHandler';
import Hydration from '../utils/Hydration';

export default class UpdatePageVersionChoice extends UpdatePage {
  protected stepCode = 'version-choice';
  private radioLoadingClass = 'radio--show-requirements-loader';

  constructor() {
    super();
  }

  public mount = () => {
    this.initStepper();
    if (!this.form) return;

    this.form.addEventListener('change', this.saveForm.bind(this));
    this.form.addEventListener('submit', this.handleSubmit);

    this.onlineCardParent?.addEventListener(Hydration.hydrationEventName, this.handleHydrate);
    this.localCardParent?.addEventListener(Hydration.hydrationEventName, this.handleHydrate);

    this.toggleNextButton();
    this.addListenerToCheckRequirementsAgainButtons();
  };

  public beforeDestroy = () => {
    if (!this.form) return;
    this.form.removeEventListener('change', this.saveForm);
    this.form.removeEventListener('submit', this.handleSubmit);
    this.onlineCardParent?.removeEventListener(Hydration.hydrationEventName, this.toggleNextButton);
    this.localCardParent?.removeEventListener(Hydration.hydrationEventName, this.toggleNextButton);
    this.checkRequirementsAgainButtons?.forEach((element) => {
      element.removeEventListener('click', this.saveForm);
    });
  };

  private sendForm = (routeToSend: string) => {
    const formData = new FormData(this.form!);
    api.post(routeToSend, formData);
  };

  private addListenerToCheckRequirementsAgainButtons = () => {
    if (this.checkRequirementsAgainButtons?.length) {
      this.checkRequirementsAgainButtons.forEach((element) => {
        element.addEventListener('click', this.saveForm);
      });
    }
  };

  private handleHydrate = () => {
    this.toggleNextButton();
    this.addListenerToCheckRequirementsAgainButtons();
  };

  private toggleNextButton = () => {
    if (this.currentChannelRequirementsAreOk) {
      this.submitButton?.removeAttribute('disabled');
    } else {
      this.submitButton?.setAttribute('disabled', 'true');
    }
  };

  private saveForm = () => {
    const routeToSave = this.form!.dataset.routeToSave;

    if (!routeToSave) {
      throw new Error('No route to save form provided. Impossible to save form.');
    }

    let currentInputCheck = null;

    if (this.onlineInputIsChecked) {
      currentInputCheck = this.onlineInputElement!;
    }

    if (this.localInputIsCheckAndFullFilled) {
      currentInputCheck = this.localInputElement!;
    }

    if (currentInputCheck) {
      currentInputCheck.removeAttribute('data-requirements-are-ok');
      this.toggleNextButton();
      currentInputCheck.classList.add(this.radioLoadingClass);
      this.sendForm(routeToSave);
    }
  };

  private handleSubmit = (event: Event) => {
    event.preventDefault();
    const routeToSubmit = this.form!.dataset.routeToSubmit;

    if (!routeToSubmit) {
      throw new Error('No route to submit form provided. Impossible to submit form.');
    }

    this.sendForm(routeToSubmit);
  };

  // global form
  private get form(): HTMLFormElement | null {
    return document.forms.namedItem('version_choice');
  }

  private get submitButton(): HTMLButtonElement | undefined {
    return this.form
      ? (Array.from(this.form.elements).find(
          (element) => element instanceof HTMLButtonElement && element.type === 'submit'
        ) as HTMLButtonElement | undefined)
      : undefined;
  }

  private get currentChannelRequirementsAreOk(): boolean {
    if (this.onlineInputIsChecked) {
      return this.onlineInputElement!.dataset.requirementsAreOk === '1';
    }
    if (this.localInputIsCheckAndFullFilled) {
      return this.localInputElement!.dataset.requirementsAreOk === '1';
    }
    return false;
  }

  private get checkRequirementsAgainButtons(): HTMLButtonElement[] | undefined {
    return this.form
      ? (Array.from(this.form.elements).filter(
          (element): element is HTMLButtonElement =>
            element instanceof HTMLButtonElement &&
            element.dataset.action === 'check-requirements-again'
        ) as HTMLButtonElement[])
      : undefined;
  }

  // online option
  private get onlineCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_online') as HTMLDivElement | undefined;
  }

  private get onlineInputElement(): HTMLInputElement | undefined {
    return this.form?.elements.namedItem('online') as HTMLInputElement | undefined;
  }

  private get onlineInputIsChecked(): boolean {
    return (this.onlineInputElement && this.onlineInputElement.checked) || false;
  }

  // local option
  private get localCardParent(): HTMLDivElement | undefined {
    return document.getElementById('radio_card_archive') as HTMLDivElement | undefined;
  }

  private get localInputElement(): HTMLInputElement | undefined {
    return this.form?.elements.namedItem('local') as HTMLInputElement | undefined;
  }

  private get localInputIsChecked(): boolean {
    return this.localInputElement?.checked || false;
  }

  private get archiveZipSelectElement(): HTMLSelectElement | undefined {
    return this.form?.elements.namedItem('archive_zip') as HTMLSelectElement | undefined;
  }

  private get archiveZipIsFilled(): boolean {
    return !!this.archiveZipSelectElement?.value;
  }

  private get archiveXmlSelectElement(): HTMLSelectElement | undefined {
    return this.form!.elements.namedItem('archive_xml') as HTMLSelectElement | undefined;
  }

  private get archiveXmlIsFilled(): boolean {
    return (this.archiveXmlSelectElement && !!this.archiveXmlSelectElement.value) || false;
  }

  private get localInputIsCheckAndFullFilled(): boolean {
    return this.localInputIsChecked && this.archiveZipIsFilled && this.archiveXmlIsFilled;
  }
}
