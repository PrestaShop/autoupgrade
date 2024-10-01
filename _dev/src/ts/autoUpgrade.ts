import RouteHandler from './routing/RouteHandler';
import ScriptHandler from './routing/ScriptHandler';

export const routeHandler = new RouteHandler();

export const scriptHandler = new ScriptHandler();

export default { routeHandler, scriptHandler };
