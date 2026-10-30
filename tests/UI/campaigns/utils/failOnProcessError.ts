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

import {Page} from '@playwright/test';

/**
 * Resolves with the result of waitForSuccess, or throws as soon as the backup/update/restore process
 * displays its error alert, instead of waiting for a success message that will never come.
 */
export default async function failOnProcessError<T>(page: Page, waitForSuccess: Promise<T>): Promise<T> {
  const errorAlert = page.locator('#error-alert:not(.hidden)');
  const processError = errorAlert.waitFor({timeout: 5000_000}).then(
    async () => {
      throw new Error(`The process failed: ${await errorAlert.locator('.alert__title').textContent()}`);
    },
    // The page was closed or the timeout was reached: let waitForSuccess report it
    () => waitForSuccess,
  );

  return Promise.race([waitForSuccess, processError]);
}
