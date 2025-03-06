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
import { AnalyticsBrowser } from '@segment/analytics-next';
import { maskSensitiveInfoInUrl } from '../utils/urlUtils';

class Analytics {
  analytics: AnalyticsBrowser;

  public constructor() {
    this.analytics = AnalyticsBrowser.load(
      { writeKey: 'RM87m03McDSL4Fvm3GJ3piBPbAL3Fa2i' },
      { disableClientPersistence: true }
    );
    this.analytics.identify(window.AutoUpgradeVariables.anonymous_id);
  }

  public track = (event: string, properties?: object) => {
    this.analytics.track(
      event,
      {
        module: 'autoupgrade',
        ...this.#getDefaultProperties(),
        ...properties
      },
      {
        page: this.#getMaskedPageData()
      }
    );
  };

  #getDefaultProperties = (): object => {
    return {
      module: 'autoupgrade',
      autoupgrade_version: window.AutoUpgradeVariables.module_version,
      php_version: window.AutoUpgradeVariables.php_version,
      ps_version: window.AutoUpgradeVariables.ps_version,
      bo_language: window.AutoUpgradeVariables.bo_language,
      bo_timezone: window.AutoUpgradeVariables.bo_timezone
    };
  };

  #getMaskedPageData = (): object => {
    const adminDir = window.AutoUpgradeVariables.admin_dir;
    return {
      path: maskSensitiveInfoInUrl(window.location.pathname, adminDir),
      referrer: maskSensitiveInfoInUrl(document.referrer, adminDir),
      url: maskSensitiveInfoInUrl(window.location.href, adminDir)
    };
  };
}

const analytics = new Analytics();

export default analytics;
