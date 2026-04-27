/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DialogContainer from './components/DialogContainer';
import RouteHandler from './routing/RouteHandler';
import ScriptHandler from './routing/ScriptHandler';
import analyticsInstance from '../api/SegmentApi';

export const analytics = analyticsInstance;
export const routeHandler = new RouteHandler();

export const dialogContainer = new DialogContainer();
export const scriptHandler = new ScriptHandler();

export default { routeHandler, scriptHandler, dialogContainer, analytics };
