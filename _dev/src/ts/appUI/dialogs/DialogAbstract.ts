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
import DomLifecycle from '../types/DomLifecycle';
import api from '../api/RequestHandler';
import DialogContainer from '../components/DialogContainer';

export default abstract class DialogAbstract implements DomLifecycle {
  public mount = (): void => {
    this.form.addEventListener('submit', this.onSubmit);
  };

  public beforeDestroy = (): void => {
    this.form.removeEventListener('submit', this.onSubmit);
  };

  abstract get form(): HTMLFormElement;

  protected onSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToSubmit!, new FormData(form));

    this.dispatchDialogContainerOkEvent(event);
  };

  protected dispatchDialogContainerOkEvent = (event: SubmitEvent): void => {
    const target = event.target ? (event.target as HTMLElement) : null;
    const dialog = target?.closest('.dialog');
    dialog?.dispatchEvent(new Event(DialogContainer.okEvent, { bubbles: true }));
  };
}
