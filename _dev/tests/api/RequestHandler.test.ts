import baseApi from '../../src/ts/api/baseApi';
import { ApiResponse } from '../../src/ts/types/apiTypes';
import Hydration from '../../src/ts/utils/Hydration';
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
    (Hydration as jest.Mock).mockClear();
  });

  it('should append admin_dir to FormData and call baseApi.post', () => {
    const formData = new FormData();
    const route = 'some_route';

    (baseApi.post as jest.Mock).mockResolvedValue({ data: {} });

    requestHandler.post(route, formData);

    expect(formData.get('dir')).toBe('/admin_directory');
    expect(baseApi.post).toHaveBeenCalledWith('', formData, { params: { route } });
  });

  it('should handle response with next_route', async () => {
    const response: ApiResponse = { next_route: 'next_route' };
    (baseApi.post as jest.Mock).mockResolvedValue({ data: response });

    const formData = new FormData();
    const route = 'some_route';

    await requestHandler.post(route, formData);

    expect(baseApi.post).toHaveBeenCalledTimes(2);
    expect(baseApi.post).toHaveBeenNthCalledWith(1, '', formData, { params: { route } });
    expect(baseApi.post).toHaveBeenNthCalledWith(2, '', formData, {
      params: { route: 'next_route' }
    });
  });

  it('should handle hydration response', async () => {
    const response: ApiResponse = {
      hydration: true,
      new_content: 'new content',
      parent_to_update: 'parent',
      new_route: 'home_page'
    };

    (baseApi.post as jest.Mock).mockResolvedValue({ data: response });

    const formData = new FormData();
    const route = 'some_route';

    await requestHandler.post(route, formData);

    expect(mockHydrate).toHaveBeenCalledTimes(1);
    expect(mockHydrate).toHaveBeenCalledWith(response, undefined);
  });
});
