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
import PageAbstract from './PageAbstract';

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

  private handleSubmit = async (event: Event) => {
    event.preventDefault();
    const routeToSubmit = this.form?.dataset.routeToSubmit;

    if (routeToSubmit) {
      this.submitButton?.classList.add('btn--loading');
      this.submitButton?.setAttribute('inert', '');

      const formData = new FormData(this.form);
      await api.post(routeToSubmit, formData);

      this.submitButton?.classList.remove('btn--loading');
      this.submitButton?.removeAttribute('inert');
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
