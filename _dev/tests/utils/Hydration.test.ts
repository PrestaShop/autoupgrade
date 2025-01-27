/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
import Hydration from '../../src/ts/utils/Hydration';
import { ApiResponseHydration } from '../../src/ts/types/apiTypes';
import RouteHandler from '../../src/ts/routing/RouteHandler';
import ScriptHandler from '../../src/ts/routing/ScriptHandler';
import { dialogContainer } from '../../src/ts/autoUpgrade';
import SpyInstance = jest.SpyInstance;

const setNewRouteMock = jest.spyOn(RouteHandler.prototype, 'setNewRoute');
const unloadRouteScriptMock = jest.spyOn(ScriptHandler.prototype, 'unloadScriptType');
const loadScriptMock = jest.spyOn(ScriptHandler.prototype, 'loadScript');

jest.mock('../../src/ts/components/DialogContainer', () => {
  return jest.fn().mockImplementation(() => {
    return {
      mount: jest.fn(),
      beforeDestroy: jest.fn()
    };
  });
});

jest.mock('../../src/ts/pages/ErrorPage', () => {
  return jest.fn().mockImplementation(() => {
    return {
      mount: () => {},
      beforeDestroy: () => {}
    };
  });
});

jest.mock('../../src/ts/pages/HomePage', () => {
  return jest.fn().mockImplementation(() => {
    return {
      mount: () => {},
      beforeDestroy: () => {}
    };
  });
});

jest.mock('../../src/ts/pages/UpdatePageBackupOptions', () => {
  return jest.fn().mockImplementation(() => ({
    mount: () => {},
    beforeDestroy: () => {
      const element = document.getElementById('my_paragraph');
      if (!element) {
        throw new Error(
          'Script unloaded too late, the element has already been removed from the DOM'
        );
      }
    }
  }));
});

describe('Hydration', () => {
  let hydration: Hydration;
  let debugSpy: SpyInstance;

  beforeEach(() => {
    debugSpy = jest.spyOn(console, 'debug').mockImplementation(() => {});
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
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: undefined
    };

    hydration.hydrate(response);

    const updatedElement = document.getElementById('parent');
    expect(updatedElement!.innerHTML).toBe('<p>New Content</p>');
  });

  it('should call scriptHandler.loadScript when new_route is provided', () => {
    const response: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response);

    expect(loadScriptMock).toHaveBeenCalledWith('new_route_value');
    expect(debugSpy).toHaveBeenCalledWith(
      'No matching script in script types found for script with ID: new_route_value'
    );
  });

  it('should call scriptHandler.loadScript when add_script is provided', () => {
    const response: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      add_script: 'additional_script'
    };

    hydration.hydrate(response);

    expect(loadScriptMock).toHaveBeenCalledWith('additional_script');
  });

  it('should call routeHandler.setNewRoute when new_route is provided and fromPopState is false', () => {
    const response: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response);

    expect(setNewRouteMock).toHaveBeenCalledWith('new_route_value');
  });

  it('should not call routeHandler.setNewRoute when fromPopState is true', () => {
    const response: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response, true);

    expect(setNewRouteMock).not.toHaveBeenCalled();
  });

  it('should not update the content if the element does not exist', () => {
    const response: ApiResponseHydration = {
      kind: 'hydrate',
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
      kind: 'hydrate',
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

  it('should not refresh the dialog container if the DOM is untouched', () => {
    const response: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'non_existent_id'
    };

    hydration.hydrate(response);

    // Called on Init
    expect(dialogContainer.mount).toHaveBeenCalledTimes(1);
  });
});

describe('Hydration and scripts lifecycle', () => {
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

  it('should unload the current script safely before loading the next one', () => {
    const initialResponse: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p id="my_paragraph">Old Content</p>`,
      parent_to_update: 'parent',
      new_route: 'update-page-backup-options'
    };
    hydration.hydrate(initialResponse);

    expect(setNewRouteMock).toHaveBeenCalledTimes(1);
    expect(unloadRouteScriptMock).toHaveBeenCalledTimes(1);

    const nextResponse: ApiResponseHydration = {
      kind: 'hydrate',
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'home-page'
    };
    hydration.hydrate(nextResponse);

    expect(setNewRouteMock).toHaveBeenCalledTimes(2);
    expect(unloadRouteScriptMock).toHaveBeenCalledTimes(2);
  });
});
