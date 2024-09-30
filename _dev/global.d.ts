interface AutoUpgradeVariables {
  token: string;
  admin_url: string;
  admin_dir: string;
}

declare global {
  interface Window {
    AutoUpgradeVariables: AutoUpgradeVariables;
  }

  const AutoUpgradeVariables: AutoUpgradeVariables;
}

export {};
