beforeAll(() => {
  window.AutoUpgrade = {
    variables: {
      token: 'test-token',
      admin_url: 'http://localhost',
      admin_dir: '/admin'
    },
    classes: {
      ScriptHandler: undefined,
      RouteHandler: undefined,
      RequestHandler: undefined
    }
  };
});
