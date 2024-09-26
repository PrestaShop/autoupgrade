import ScriptHandler from './src/ts/routing/ScriptHandler';

interface AutoUpgrade {
  token: string;
  admin_url: string;
  admin_dir: string;
}

declare global {
  interface Window {
    AutoUpgradeScriptHandler: ScriptHandler;
    AutoUpgrade: AutoUpgrade;
  }

  const AutoUpgrade: AutoUpgrade;
}

export {};
