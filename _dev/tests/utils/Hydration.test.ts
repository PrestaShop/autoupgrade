import Hydration from '../../src/ts/utils/Hydration';
import { ApiResponseHydration } from '../../src/ts/types/apiTypes';
import { routeHandler, scriptHandler } from '../../src/ts/autoUpgrade';

jest.mock('../../src/ts/autoUpgrade', () => ({
  routeHandler: {
    setNewRoute: jest.fn()
  },
  scriptHandler: {
    updateRouteScript: jest.fn()
  }
}));

describe('Hydration', () => {
  let hydration: Hydration;

  beforeEach(() => {
    hydration = new Hydration();
    document.body.innerHTML = `
      <div id="parent">
        <p>Old Content</p>
      </div>
    `;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('should update the innerHTML of the target element', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: undefined
    };

    hydration.hydrate(response);

    const updatedElement = document.getElementById('parent');
    expect(updatedElement!.innerHTML).toBe('<p>New Content</p>');
  });

  it('should call scriptHandler.updateRouteScript when new_route is provided', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response);

    expect(scriptHandler.updateRouteScript).toHaveBeenCalledWith('new_route_value');
  });

  it('should call routeHandler.setNewRoute when new_route is provided and fromPopState is false', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response);

    expect(routeHandler.setNewRoute).toHaveBeenCalledWith('new_route_value');
  });

  it('should not call routeHandler.setNewRoute when fromPopState is true', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response, true);

    expect(routeHandler.setNewRoute).not.toHaveBeenCalled();
  });

  it('should not update the content if the element does not exist', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'non_existent_id'
    };

    hydration.hydrate(response);

    const updatedElement = document.getElementById('parent');
    expect(updatedElement!.innerHTML).toBe(`
        <p>Old Content</p>
      `);
  });

  it('should dispatch the hydration event on the updated element', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: undefined
    };

    const updatedElement = document.getElementById('parent');
    const dispatchEventSpy = jest.spyOn(updatedElement!, 'dispatchEvent');

    hydration.hydrate(response);

    expect(dispatchEventSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: Hydration.hydrationEventName
      })
    );
  });
});
