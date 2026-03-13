/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { sendUserFeedback } from '../api/sentryApi';
import { Feedback, FeedbackFields, Logs } from '../types/sentryApi';
import { logStore } from '../store/LogStore';
import { formatLogsMessages } from '../utils/logsUtils';
import DialogAbstract from './DialogAbstract';
import ErrorPageBuilder from '../components/ErrorPageBuilder';
import WrapperCopy from '../components/WrapperCopy';

export default class SendErrorReportDialog extends DialogAbstract {
  protected readonly formId = 'form-error-feedback';
  #wrapperCopy: WrapperCopy;

  constructor() {
    super();
    this.#wrapperCopy = new WrapperCopy();
  }

  public mount = (): void => {
    this.form.addEventListener('submit', this.onSubmit);

    const errorMessageArea: HTMLTextAreaElement = this.form.querySelector('#errorMessage')!;
    errorMessageArea.innerText = this.#errorMessagePanelContents;

    this.#wrapperCopy.mount();
  };

  public beforeDestroy = (): void => {
    this.#wrapperCopy.beforeDestroy();
  };

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    return form;
  }

  get #errorMessagePanelContents(): string {
    if (this.#responseContents) {
      try {
        const loggedErrors = JSON.parse(this.#responseContents)?.nextQuickInfo?.join('\n\n');
        return `${this.#lastErrorMessage}\n\n${loggedErrors}`;
      } catch {
        // Sometimes we can get a response from the server that is not a JSON. In that case:
        // Do nothing
      }
    }

    return this.#lastErrorMessage;
  }

  get #lastErrorMessage(): string {
    const latestError = logStore.getErrors().pop()?.message;

    if (!latestError) {
      throw new Error('No error message found to send');
    }

    return latestError;
  }

  get #responseContents(): string | null {
    return (
      document.getElementById(ErrorPageBuilder.externalAdditionalContentsPanelId)?.textContent ||
      null
    );
  }

  onSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const attachments = {
      logs: this.#getLogs(),
      other: new Map<string, string>()
    };
    const responseContents = this.#responseContents;
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
