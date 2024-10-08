import UpdatePage from './UpdatePage';
import api from '../api/RequestHandler';

export default class UpdatePageVersionChoice extends UpdatePage {
  protected stepCode = 'version-choice';
  private radioLoadingClass = 'radio--show-requirements-loader';
  form?: HTMLFormElement;
  submitButton?: HTMLButtonElement;

  constructor() {
    super();
    const form = document.forms.namedItem('version_choice');
    if (form) {
      this.form = form;

      const submitButton = Array.from(this.form.elements).find(
        (element) => element instanceof HTMLButtonElement && element.type === 'submit'
      ) as HTMLButtonElement | null;

      if (submitButton) {
        this.submitButton = submitButton;
      }
    }
  }

  public mount() {
    this.initStepper();
    if (this.form) {
      this.form.addEventListener('change', this.handleSave.bind(this));
      this.form.addEventListener('submit', this.handleSubmit);
    }
  }

  public beforeDestroy = () => {
    if (this.form) {
      this.form.removeEventListener('change', this.handleSave);
      this.form.removeEventListener('submit', this.handleSubmit);
    }
  };

  private sendForm(routeToSend: string) {
    const formData = new FormData(this.form);
    api.post(routeToSend, formData);
  }

  private handleSave() {
    const routeToSave = this.form!.dataset.routeToSave;

    if (!routeToSave) {
      throw new Error('No route to save form provided. Impossible to save form.');
    }

    const onlineInputElement = this.form!.elements.namedItem('online') as HTMLInputElement | null;
    if (onlineInputElement && onlineInputElement.checked) {
      onlineInputElement.classList.add(this.radioLoadingClass);
      this.sendForm(routeToSave);
    }

    const localInputElement = this.form!.elements.namedItem('local') as HTMLInputElement | null;
    const archiveZipSelectElement = this.form!.elements.namedItem(
      'archive_zip'
    ) as HTMLSelectElement | null;
    const archiveXmlSelectElement = this.form!.elements.namedItem(
      'archive_xml'
    ) as HTMLSelectElement | null;
    if (
      localInputElement &&
      archiveZipSelectElement &&
      archiveXmlSelectElement &&
      localInputElement.checked &&
      archiveZipSelectElement.value &&
      archiveXmlSelectElement.value
    ) {
      localInputElement.classList.add(this.radioLoadingClass);
      this.sendForm(routeToSave);
    }
  }

  private handleSubmit(event: Event) {
    event.preventDefault();
    const routeToSubmit = this.form!.dataset.routeToSubmit;

    if (!routeToSubmit) {
      throw new Error('No route to submit form provided. Impossible to submit form.');
    }

    this.sendForm(routeToSubmit);
  }
}
