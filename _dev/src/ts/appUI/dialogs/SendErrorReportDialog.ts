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
import { sendUserFeedback } from '../api/sentryApi';
import { Feedback, FeedbackFields, Logs } from '../types/sentryApi';
import { logStore } from '../store/LogStore';
import { formatLogsMessages } from '../utils/logsUtils';
import DialogAbstract from './DialogAbstract';
import ErrorPageBuilder from '../components/ErrorPageBuilder';

export default class SendErrorReportDialog extends DialogAbstract {
  protected readonly formId = 'form-error-feedback';

  public mount = (): void => {
    this.form.addEventListener('submit', this.onSubmit);

    const errorMessageArea: HTMLTextAreaElement = this.form.querySelector('#errorMessage')!;
    errorMessageArea.value = this.#lastErrorMessage;
  };

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    return form;
  }

  get #lastErrorMessage(): string {
    const latestError = logStore.getErrors().pop()?.message;

    if (!latestError) {
      throw new Error('No error message found to send');
    }

    return latestError;
  }

  onSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const attachments = {
      logs: this.#getLogs(),
      other: new Map<string, string>()
    };
    const responseContents = document.getElementById(
      ErrorPageBuilder.externalAdditionalContentsPanelId
    )?.textContent;
    if (responseContents) {
      attachments.other.set('response_raw.txt', responseContents);
    }
    const feedback = this.#getFeedback(event.target as HTMLFormElement);

    sendUserFeedback(this.#lastErrorMessage, attachments, feedback);

    this.dispatchDialogContainerOkEvent(event);
  };

  #getLogs(): Logs {
    return {
      logs: formatLogsMessages(logStore.getLogs()),
      warnings: formatLogsMessages(logStore.getWarnings()),
      errors: formatLogsMessages(logStore.getErrors())
    };
  }

  #getFeedback(form: HTMLFormElement): Feedback {
    const formData = new FormData(form);
    const feedback: Feedback = {};

    Object.values(FeedbackFields).forEach((field) => {
      const value = formData.get(field);
      if (value && typeof value === 'string') {
        feedback[field] = value;
      }
    });

    return feedback;
  }
}
