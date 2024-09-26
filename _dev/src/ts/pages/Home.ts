import RequestHandler from '../api/RequestHandler';

export default class HomePage {
  form: HTMLFormElement | null;
  submitButton: HTMLButtonElement | null;

  constructor() {
    this.form = document.forms.namedItem('next_page');
    this.submitButton = this.getSubmitButton();
  }

  public mount = () => {
    if (this.form) {
      this.checkForm();
      this.form.addEventListener('change', this.checkForm);
      this.form.addEventListener('submit', this.handleSubmit);
    }
  };

  private getSubmitButton = (): HTMLButtonElement | null => {
    if (!this.form) return null;
    const elements = Array.from(this.form.elements);
    return elements.find(
      (element) => element instanceof HTMLButtonElement && element.type === 'submit'
    ) as HTMLButtonElement | null;
  };

  private checkForm = () => {
    if (this.form?.checkValidity()) {
      this.submitButton?.removeAttribute('disabled');
    } else {
      this.submitButton?.setAttribute('disabled', 'true');
    }
  };

  private handleSubmit = (event: Event) => {
    event.preventDefault();
    if (this.form) {
      const formData = new FormData(this.form);

      // TODO: add route to call inside data form
      new RequestHandler().post('home-page-form', formData);
    }
  };

  public beforeDestroy = () => {
    if (this.form) {
      this.form.removeEventListener('change', this.checkForm);
      this.form.removeEventListener('submit', this.handleSubmit);
    }
  };
}
