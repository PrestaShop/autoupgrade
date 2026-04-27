/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DialogAbstract from './DialogAbstract';
import api from '../api/RequestHandler';
import { analytics } from '../main';

export default class StartBackupDialog extends DialogAbstract {
  protected readonly formId = 'form-confirm-backup';

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  protected onSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    const dataOptions = new FormData(document.forms.namedItem('update-backup-page-form')!);

    await api.post(form.dataset.routeToSubmit!, new FormData(form));

    analytics.track('[SUE] Backup configured', {
      backup_images: !!dataOptions.get('keep_images')
    });

    this.dispatchDialogContainerOkEvent(event);
  };
}
