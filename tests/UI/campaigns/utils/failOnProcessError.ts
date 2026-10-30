/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
