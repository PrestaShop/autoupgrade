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
import DialogAbstract from './DialogAbstract';
import api from '../api/RequestHandler';
import { analytics } from '../autoUpgrade';

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
      backup_images: Boolean(dataOptions.get('PS_AUTOUP_KEEP_IMAGES'))
    });

    this.dispatchDialogContainerOkEvent(event);
  };
}
