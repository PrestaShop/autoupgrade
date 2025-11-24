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

/**
 * While we run a process of update or restore, we alter the store in many ways.
 * We try to control when to initialize the Core, or emptying the cache when necessary.
 *
 * If an ajax request is sent to the shop in parallel, we encounter side effects
 * of cache partially generated, HTTP 500 in the logs caused by PrestaShop being modified etc.
 *
 * To avoid these issues, Update Assistant will prevent requests to reach the shop.
 *
 * @see https://github.com/PrestaShop/PrestaShop/issues/39509
 */
const PREFIX = "Update Assistant's guard:";
let filterRequestsToShop = false;

(function (): void {
  // Save references to the original methods
  const origOpen = XMLHttpRequest.prototype.open;

  XMLHttpRequest.prototype.open = function (...args: [method: string, url: string | URL]) {
    const url = args[1];
    const filtered = !isRequestAllowed(String(url));

    if (filterRequestsToShop && filtered) {
      console.debug(`${PREFIX} Request filtered (${url})`);
      this.abort();
    } else {
      // @ts-expect-error because we're sending the same params as we receive them. Typescript is lost becaused of the overloads.
      origOpen.apply(this, args);
    }
  };
})();

export const isRequestAllowed = (url: string): boolean => {
  // Block all requests targeting the shop unless they are for Update Assistant
  if (!url.startsWith('/') && !url.includes(window.location.host)) {
    return true;
  }
  return url.includes('autoupgrade');
};

export function disableFilteringOfRequestsToShop(): void {
  console.debug(`${PREFIX} Requests to the shop are allowed.`);

  filterRequestsToShop = false;
}

export function enableFilteringOfRequestsToShop(): void {
  console.debug(`${PREFIX} Requests to the shop are now filtered.`);

  filterRequestsToShop = true;
}
