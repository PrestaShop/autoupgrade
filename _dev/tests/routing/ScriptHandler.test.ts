/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import ScriptHandler from '../../src/ts/appUI/routing/ScriptHandler';
import HomePage from '../../src/ts/appUI/pages/HomePage';
import UpdatePageVersionChoice from '../../src/ts/appUI/pages/UpdatePageVersionChoice';
import { routeHandler } from '../../src/ts/appUI/main';

jest.mock('../../src/ts/appUI/main', () => ({
  routeHandler: {
    getCurrentRoute: jest.fn()
  }
}));

const homeMount = jest.fn();
const homeDestroy = jest.fn();
jest.mock('../../src/ts/appUI/pages/HomePage', () => {
  return jest.fn().mockImplementation(() => ({
    mount: homeMount,
    beforeDestroy: homeDestroy
  }));
});

const updateMount = jest.fn();
const updateDestroy = jest.fn();
jest.mock('../../src/ts/appUI/pages/UpdatePageVersionChoice', () => {
  return jest.fn().mockImplementation(() => ({
    mount: updateMount,
    beforeDestroy: updateDestroy
  }));
});

describe('ScriptHandler', () => {
  let scriptHandler: ScriptHandler;

  beforeEach(() => {
    jest.clearAllMocks();
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('home-page');
  });

  it('should load the correct script based on the default route (home-page)', () => {
    scriptHandler = new ScriptHandler();

    expect(HomePage).toHaveBeenCalledTimes(1);

    expect(homeMount).toHaveBeenCalledTimes(1);
  });

  it('should load the correct script based on the current route (update-page-version-choice)', () => {
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('update-page-version-choice');

    scriptHandler = new ScriptHandler();

    expect(UpdatePageVersionChoice).toHaveBeenCalledTimes(1);
    expect(updateMount).toHaveBeenCalledTimes(1);
  });

  it('should update the route script and destroy the previous one', () => {
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('home-page');
    scriptHandler = new ScriptHandler();

    expect(homeMount).toHaveBeenCalledTimes(1);

    scriptHandler.loadScript('update-page-version-choice');

    expect(homeDestroy).toHaveBeenCalledTimes(1);
    expect(UpdatePageVersionChoice).toHaveBeenCalledTimes(1);
    expect(updateMount).toHaveBeenCalledTimes(1);
  });

  it('should catch en log warning if no matching class is found for the route', () => {
    const consoleDebugSpy = jest.spyOn(console, 'debug').mockImplementation();
    const route = 'unknown-route';
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue(route);

    scriptHandler = new ScriptHandler();

    expect(consoleDebugSpy).toHaveBeenCalledWith(
      `No matching script in script types found for script with ID: ${route}`
    );
  });

  it('should catch and log errors if page instantiation or mount fails', () => {
    const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation();
    const errorMessage = 'Test error';

    updateMount.mockImplementation(() => {
      throw new Error(errorMessage);
    });

    const route = 'update-page-version-choice';
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('home-route');

    scriptHandler = new ScriptHandler();
    scriptHandler.loadScript(route);

    expect(consoleErrorSpy).toHaveBeenCalledWith(
      `Failed to load script with ID ${route}:`,
      expect.any(Error)
    );
  });
});
