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
import { ApiResponseAction } from '../types/apiTypes';
import { ProgressTrackerCallbacks } from '../types/Process';

export default class Process {
  #callbacks: ProgressTrackerCallbacks;

  /**
   * @constructor
   * @param {Callbacks} callbacks - Lifecycle callbacks for handling process events:
   * - `onProcess`: Triggered for each valid API response.
   * - `onError`: Triggered when an error occurs in the process.
   * - `onProcessEnd`: Triggered when the process ends successfully.
   */
  constructor(callbacks: ProgressTrackerCallbacks) {
    this.#callbacks = callbacks;
  }

  /**
   * @public
   * @async
   * @param {string} action - The initial action to start the process.
   * @returns {Promise<void>}
   * @description Initiates the process by launching the specified action.
   * The process continues sequentially based on API responses.
   */
  public startProcess = async (action: string): Promise<void> => {
    await this.#launchAction(action);
  };

  /**
   * @private
   * @async
   * @param {string} initialAction - The initial action to start the sequence.
   * @returns {Promise<void>}
   * @description Manages the sequence of API calls, following the `next` action provided
   * in the responses, until the process ends or an error occurs.
   */
  #launchAction = async (initialAction: string): Promise<void> => {
    let processEnd = false;
    let action = initialAction;

    while (processEnd === false) {
      const response = (await api.postAction(action)) as ApiResponseAction;
      this.#handleResponseAction(response);
      if (!response.next || response.error === true || response.next === 'Error') {
        processEnd = true;
      }
      action = response.next;
    }
  };

  /**
   * @private
   * @param {ApiResponseAction} response - The API response containing process data,
   * the next action, or an error flag.
   * @returns {void}
   * @description Handles API responses by invoking the appropriate callback.
   * Calls `onError` if an error is detected. Otherwise, it triggers `onProcess` or
   * `onProcessEnd` based on the response's `next` field.
   */
  #handleResponseAction = (response: ApiResponseAction): void => {
    if (response.error === true || response.next === 'Error') {
      this.#callbacks.onError(response);
      return;
    }

    this.#callbacks.onProcessResponse(response);

    if (!response.next) {
      this.#callbacks.onProcessEnd(response);
    }
  };
}
