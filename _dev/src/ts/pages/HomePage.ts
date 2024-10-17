import PageAbstract from './PageAbstract';
import api from '../api/RequestHandler';

export default class HomePage extends PageAbstract {
  constructor() {
    super();
    if (!this.form) {
      throw new Error("The form wasn't found inside DOM. HomePage can't be initiated properly");
    }
    if (!this.submitButton) {
      throw new Error(
        "The submit button wasn't found inside DOM. HomePage can't be initiated properly"
      );
    }
  }

  public mount = () => {
    if (this.form) {
      this.checkForm();
      this.form.addEventListener('change', this.checkForm);
      this.form.addEventListener('submit', this.handleSubmit);
    }
  };

  public beforeDestroy = () => {
    if (this.form) {
      this.form.removeEventListener('change', this.checkForm);
      this.form.removeEventListener('submit', this.handleSubmit);
    }
  };

  private checkForm = () => {
    if (this.formIsValid) {
      this.submitButton?.removeAttribute('disabled');
    } else {
      this.submitButton?.setAttribute('disabled', 'true');
    }
  };

  private handleSubmit = (event: Event) => {
    event.preventDefault();
    const routeToSubmit = this.form?.dataset.routeToSubmit;

    if (routeToSubmit) {
      const formData = new FormData(this.form);
      api.post(routeToSubmit, formData);
    }
  };

  private get form(): HTMLFormElement | null {
    return document.forms.namedItem('next_page');
  }

  private get formIsValid(): boolean {
    return this.form ? this.form.checkValidity() : false;
  }

  private get submitButton(): HTMLButtonElement | undefined {
    return this.form
      ? (Array.from(this.form.elements).find(
          (element) => element instanceof HTMLButtonElement && element.type === 'submit'
        ) as HTMLButtonElement | undefined)
      : undefined;
  }
}
