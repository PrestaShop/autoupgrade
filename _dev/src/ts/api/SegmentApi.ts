import { AnalyticsBrowser } from '@segment/analytics-next';
import { maskSensitiveInfoInUrl } from '../utils/urlUtils';

class Analytics {
  analytics: AnalyticsBrowser;

  public constructor() {
    this.analytics = new AnalyticsBrowser();
    this.analytics.load({ writeKey: 'RM87m03McDSL4Fvm3GJ3piBPbAL3Fa2i' });
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
