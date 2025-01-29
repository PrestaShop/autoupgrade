import { ApiError } from '../types/apiTypes';

export default class ErrorPageBuilder {
  public constructor(private readonly errorElement: DocumentFragment) {}

  /**
   * Replace the id of the cloned element
   */
  public updateId(type: ApiError['type']): void {
    const errorChild = this.errorElement.getElementById('ua_error_placeholder');
    if (errorChild) {
      errorChild.id = `ua_error_${type}`;
    }
  }

  /**
   * If code is a HTTP error number (i.e 404, 500 etc.), let's change the text in the left column with it.
   */
  public updateLeftColumn(code: ApiError['code']): void {
    if (this.#isHttpErrorCode(code)) {
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
    const userFriendlyDescriptionElement = errorDescriptionElement?.querySelector(
      `.error-page__desc-${this.#isHttpErrorCode(errorDetails.code) ? errorDetails.code : errorDetails.type}`
    );
    if (userFriendlyDescriptionElement) {
      userFriendlyDescriptionElement.classList.remove('hidden');
    } else if (errorDescriptionElement && errorDetails.type) {
      errorDescriptionElement.innerHTML = errorDetails.type;
    }
  }

  #isHttpErrorCode(code?: number): boolean {
    return typeof code === 'number' && code >= 300 && code.toString().length === 3;
  }
}
