/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import Stepper from './src/ts/appUI/utils/Stepper';

interface AutoUpgradeVariables {
  token: string;
  admin_url: string;
  admin_dir: string;
  stepper_parent_id: string;
  module_version: string;
  php_version: string;
  anonymous_id: string;
  ps_version: string;
  bo_language: string;
  bo_timezone: string;
  links: {
    help: string;
  };
  translations: Record<string, string>;
  has_opted_out_analytics: boolean;
}

declare global {
  interface Window {
    AutoUpgradeVariables: AutoUpgradeVariables;
    PageStepper: ?Stepper;
  }

  const AutoUpgradeVariables: AutoUpgradeVariables;
  const PageStepper: ?Stepper;
}

export {};
