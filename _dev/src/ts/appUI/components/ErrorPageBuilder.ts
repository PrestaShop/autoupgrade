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
import { isHttpErrorCode } from '../api/axiosError';
import { ApiError } from '../types/apiTypes';

export default class ErrorPageBuilder {
  public static readonly externalAdditionalContentsPanelId = 'log-additional-contents';

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
    const userFriendlyDescriptionElement = errorDescriptionElement?.querySelector(
      `.error-page__desc-${isHttpErrorCode(errorDetails.code) ? errorDetails.code : errorDetails.type}`
    );
    if (userFriendlyDescriptionElement) {
      userFriendlyDescriptionElement.classList.remove('hidden');
    } else if (errorDescriptionElement && errorDetails.type) {
      errorDescriptionElement.innerHTML = errorDetails.type;
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
