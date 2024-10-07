import Stepper from './src/ts/utils/Stepper';

interface AutoUpgradeVariables {
  token: string;
  admin_url: string;
  admin_dir: string;
  stepper_parent_id: string;
}

declare global {
  interface Window {
    AutoUpgradeVariables: AutoUpgradeVariables;
    UpdatePageStepper: ?Stepper;
  }

  const AutoUpgradeVariables: AutoUpgradeVariables;
  const UpdatePageStepper: ?Stepper;
}

export {};
