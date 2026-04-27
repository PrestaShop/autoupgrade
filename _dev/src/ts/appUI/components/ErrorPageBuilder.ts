/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { isHttpErrorCode } from '../api/axiosError';
import { ApiError } from '../types/apiTypes';

export default class ErrorPageBuilder {
  public static readonly externalAdditionalContentsPanelId = 'log-additional-contents';

  public constructor(private readonly errorElement: DocumentFragment) {}

  /**
   * Replace the id of the cloned element
   */
  public updateId(errorDetails: Pick<ApiError, 'code' | 'type'>): void {
    const errorChild = this.errorElement.getElementById('ua_error_placeholder');
    if (errorChild) {
      errorChild.id = `ua_error_${isHttpErrorCode(errorDetails.code) ? errorDetails.code : errorDetails.type}`;

      errorChild.dataset.errorCode = errorDetails.code?.toString();
      errorChild.dataset.errorType = errorDetails.type;
    }
  }

  /**
   * If code is a HTTP error number (i.e 404, 500 etc.), let's change the text in the left column with it.
   */
  public updateLeftColumn(code: ApiError['code']): void {
    if (isHttpErrorCode(code)) {
      const stringifiedCode = (code as number).toString().replaceAll('0', 'O');
      const errorCodeSlotElements = this.errorElement.querySelectorAll('.error-page__code-char');
      errorCodeSlotElements.forEach((element: Element, index: number) => {
        element.innerHTML = stringifiedCode[index];
      });
    } else {
      this.errorElement.querySelector('.error-page__code')?.classList.add('hidden');
    }
  }

  /**
   * Display a user friendly text related to the code if it exists, otherwise write the error code.
   */
  public updateDescriptionBlock(errorDetails: Pick<ApiError, 'code' | 'type'>): void {
    const errorDescriptionElement = this.errorElement.querySelector('.error-page__desc');
    const userFriendlyDescriptionElement =
      errorDescriptionElement?.querySelector(
        `.error-page__desc-${isHttpErrorCode(errorDetails.code) ? errorDetails.code : errorDetails.type}`
      ) || errorDescriptionElement?.querySelector('.error-page__desc-unknown');
    if (userFriendlyDescriptionElement) {
      userFriendlyDescriptionElement.classList.remove('hidden');
    }
  }

  /**
   * Store the response contents on the DOM to keep it ready to send in the report.
   */
  public updateResponseBlock(response: ApiError['additionalContents']): void {
    const errorDescriptionElement = this.errorElement.getElementById(
      ErrorPageBuilder.externalAdditionalContentsPanelId
    );
    if (errorDescriptionElement && response) {
      errorDescriptionElement.textContent = response;
    }
  }
}
