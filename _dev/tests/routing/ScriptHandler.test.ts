import ScriptHandler from '../../src/ts/routing/ScriptHandler';
import HomePage from '../../src/ts/pages/Home';
import Update from '../../src/ts/pages/Update';
import { routeHandler } from '../../src/ts/autoUpgrade';

jest.mock('../../src/ts/autoUpgrade', () => ({
  routeHandler: {
    getCurrentRoute: jest.fn()
  }
}));

const homeMount = jest.fn();
const homeDestroy = jest.fn();
jest.mock('../../src/ts/pages/Home', () => {
  return jest.fn().mockImplementation(() => ({
    mount: homeMount,
    beforeDestroy: homeDestroy
  }));
});

const updateMount = jest.fn();
const updateDestroy = jest.fn();
jest.mock('../../src/ts/pages/Update', () => {
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

  it('should load the correct script based on the current route (home-page)', () => {
    scriptHandler = new ScriptHandler();

    expect(HomePage).toHaveBeenCalledTimes(1);

    expect(homeMount).toHaveBeenCalledTimes(1);
  });

  it('should load the correct script based on the current route (update-page-version-choice)', () => {
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('update-page-version-choice');

    scriptHandler = new ScriptHandler();

    expect(Update).toHaveBeenCalledTimes(1);
    expect(updateMount).toHaveBeenCalledTimes(1);
  });

  it('should not load any script if the route does not match', () => {
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('unknown-route');

    scriptHandler = new ScriptHandler();

    expect(HomePage).not.toHaveBeenCalled();
    expect(Update).not.toHaveBeenCalled();
  });

  it('should update the route script and destroy the previous one', () => {
    (routeHandler.getCurrentRoute as jest.Mock).mockReturnValue('home-page');
    scriptHandler = new ScriptHandler();

    expect(homeMount).toHaveBeenCalledTimes(1);

    scriptHandler.updateRouteScript('update-page-version-choice');

    expect(homeDestroy).toHaveBeenCalledTimes(1);
    expect(Update).toHaveBeenCalledTimes(1);
    expect(updateMount).toHaveBeenCalledTimes(1);
  });
});
