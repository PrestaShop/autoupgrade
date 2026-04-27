/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DomLifecycle from '../../types/DomLifecycle';
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
