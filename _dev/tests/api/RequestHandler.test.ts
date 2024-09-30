import RequestHandler from '../../src/ts/api/RequestHandler';

describe('RequestHandler Tests', () => {
  let requestHandler: RequestHandler;

  beforeEach(() => {
    requestHandler = new RequestHandler(); // Instanciez votre RequestHandler
  });

  it('should initialize RequestHandler in window.AutoUpgrade', () => {
    expect(window.AutoUpgrade.classes.RequestHandler).toBe(requestHandler);
  });

  it('should use the correct admin_url in baseApi', () => {
    // Ajoutez des assertions pour vérifier que l'URL est correcte
    const expectedUrl = `${window.AutoUpgrade.variables.admin_url}/autoupgrade/ajax-upgradetab.php`;
    expect(baseApi.defaults.baseURL).toBe(expectedUrl);
  });

  // Ajoutez d'autres tests selon vos besoins
});
