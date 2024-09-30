import PageAbstract from './PageAbstract';
import api from '../api/RequestHandler';

export default class HomePage extends PageAbstract {
  form: HTMLFormElement;
  submitButton: HTMLButtonElement;

  constructor() {
    super();
    const form = document.forms.namedItem('next_page');
    if (form) {
      this.form = form;
    } else {
      throw new Error("The form wasn't found inside DOM. HomePage can't be initiated properly");
    }
    const submitButton = Array.from(this.form.elements).find(
      (element) => element instanceof HTMLButtonElement && element.type === 'submit'
    ) as HTMLButtonElement | null;
    if (submitButton) {
      this.submitButton = submitButton;
    } else {
      throw new Error(
        "The submit button wasn't found inside DOM. HomePage can't be initiated properly"
      );
    }
  }

  public mount = () => {
    this.checkForm();
    this.form.addEventListener('change', this.checkForm);
    this.form.addEventListener('submit', this.handleSubmit);
  };

  public beforeDestroy = () => {
    this.form.removeEventListener('change', this.checkForm);
    this.form.removeEventListener('submit', this.handleSubmit);
  };

  private checkForm = () => {
    if (this.form.checkValidity()) {
      this.submitButton.removeAttribute('disabled');
    } else {
      this.submitButton.setAttribute('disabled', 'true');
    }
  };

  private handleSubmit = (event: Event) => {
    event.preventDefault();
    const route = this.form.dataset.route;

    if (route) {
      const formData = new FormData(this.form);
      api.post(route, formData);
    }
  };
}
