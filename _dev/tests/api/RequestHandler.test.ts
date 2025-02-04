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
import baseApi from '../../src/ts/api/baseApi';
import { ApiResponse, ApiResponseAction } from '../../src/ts/types/apiTypes';
import { RequestHandler } from '../../src/ts/api/RequestHandler';

jest.mock('../../src/ts/api/baseApi', () => ({
  post: jest.fn()
}));

const mockHydrate = jest.fn();

jest.mock('../../src/ts/utils/Hydration', () => {
  return jest.fn().mockImplementation(() => ({
    hydrate: mockHydrate
  }));
});

describe('RequestHandler', () => {
  let requestHandler: RequestHandler;

  beforeEach(() => {
    requestHandler = new RequestHandler();
    (baseApi.post as jest.Mock).mockClear();
    mockHydrate.mockClear();
  });

  it('should handle response with next_route and make two API calls', async () => {
    const response: ApiResponse = { kind: 'next_route', next_route: 'next_route' };
    (baseApi.post as jest.Mock).mockResolvedValueOnce({ data: response });

    const formData = new FormData();
    const route = 'some_route';

    await requestHandler.post(route, formData);

    expect(baseApi.post).toHaveBeenCalledTimes(2);
    expect(baseApi.post).toHaveBeenNthCalledWith(1, '', formData, {
      params: { route },
      signal: expect.any(AbortSignal)
    });
    expect(baseApi.post).toHaveBeenNthCalledWith(2, '', undefined, {
      params: { route: 'next_route' },
      signal: expect.any(AbortSignal)
    });
  });

  it('should handle hydration response', async () => {
    const response: ApiResponse = {
      kind: 'hydrate',
      hydration: true,
      new_content: 'new content',
      parent_to_update: 'parent',
      new_route: 'home_page'
    };

    (baseApi.post as jest.Mock).mockResolvedValueOnce({ data: response });

    const formData = new FormData();
    const route = 'some_route';

    await requestHandler.post(route, formData);

    expect(mockHydrate).toHaveBeenCalledTimes(1);
    expect(mockHydrate).toHaveBeenCalledWith(response, undefined);
  });

  it('should handle action response', async () => {
    const response: ApiResponseAction = {
      kind: 'action',
      error: null,
      stepDone: false,
      next: 'Update',
      status: 'ok',
      next_desc: 'description step',
      nextQuickInfo: [],
      nextParams: {
        progressPercentage: 80
      }
    };

    const route = 'some_route';

    (baseApi.post as jest.Mock).mockResolvedValueOnce({ data: response });

    const result = await requestHandler.postAction(route);

    expect(result).toEqual(response);
    expect(baseApi.post).toHaveBeenCalledTimes(1);
  });

  it('should cancel the previous request when a new one is made', async () => {
    const formData = new FormData();
    const route = 'some_route';

    const abortSpy = jest.spyOn(AbortController.prototype, 'abort');

    await requestHandler.post(route, formData);
    await requestHandler.post(route, formData);

    expect(abortSpy).toHaveBeenCalledTimes(1);

    abortSpy.mockRestore();
  });
});
