import ScriptHandler from './src/ts/routing/ScriptHandler';
import RouteHandler from './src/ts/routing/RouteHandler';
import RequestHandler from './src/ts/api/RequestHandler';

interface AutoUpgradeVariables {
  token: string;
  admin_url: string;
  admin_dir: string;
}

interface AutoUpgrade {
  variables: AutoUpgradeVariables;
  classes: {
    ScriptHandler: ?ScriptHandler;
    RouteHandler: ?RouteHandler;
    RequestHandler: ?RequestHandler;
  };
}

declare global {
  interface Window {
    AutoUpgrade: AutoUpgrade;
  }

  const AutoUpgrade: AutoUpgrade;
}

export {};
