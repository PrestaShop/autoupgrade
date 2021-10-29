require('module-alias/register');
require('@root/globals.js');

const {expect} = require('chai');
const helper = require('prestashop_test_lib/kernel/utils/helpers');

// Get resolver
const VersionSelectResolver = require('prestashop_test_lib/kernel/resolvers/versionSelectResolver');

const configClassMap = require('@root/configClassMap.js');

const versionSelectResolver = new VersionSelectResolver(global.PS_RESOLVER_VERSION.FROM, configClassMap);

// Import pages
const loginPage = versionSelectResolver.require('BO/login/index.js');
const dashboardPage = versionSelectResolver.require('BO/dashboard/index.js');
const moduleCatalogPage = versionSelectResolver.require('BO/modules/moduleCatalog/index.js');
const moduleManagerPage = versionSelectResolver.require('BO/modules/moduleManager/index.js');

// Browser vars
let browserContext;
let page;
const moduleToInstall = {
  name: '1-Click Upgrade',
  tag: 'autoupgrade',
};

/*
Go to login page
Check PS version
Log in
Install 1-Click Upgrade module
 */
describe(`[${global.AUTOUPGRADE_VERSION}] Install '${moduleToInstall.name}' module`, async () => {
  // before and after functions
  before(async function () {
    browserContext = await helper.createBrowserContext(this.browser);

    page = await helper.newTab(browserContext);
  });

  after(async () => {
    await helper.closeBrowserContext(browserContext);
  });

  it('should login into BO with default user', async () => {
    await loginPage.goTo(page, global.BO.URL);
    await loginPage.login(page, global.BO.EMAIL, global.BO.PASSWD);
    await dashboardPage.closeOnboardingModal(page);

    const pageTitle = await dashboardPage.getPageTitle(page);
    await expect(pageTitle).to.contains(dashboardPage.pageTitle);
  });

  it('should go to Modules Catalog page', async () => {
    if (global.PS_VERSION.includes('1.7.4')) {
      await dashboardPage.goToSubMenu(
        page,
        dashboardPage.modulesParentLink,
        dashboardPage.moduleManagerLink,
      );

      await moduleManagerPage.goToSelectionPage(page);
    } else {
      await dashboardPage.goToSubMenu(
        page,
        dashboardPage.modulesParentLink,
        dashboardPage.moduleCatalogueLink,
      );
    }

    const pageTitle = await moduleCatalogPage.getPageTitle(page);
    await expect(pageTitle).to.contains(moduleCatalogPage.pageTitle);
  });

  it('should search 1-Click Upgrade module', async () => {
    const isModuleVisible = await moduleCatalogPage.searchModule(page, moduleToInstall.tag, moduleToInstall.name);

    await expect(isModuleVisible).to.be.true;
  });

  it('should install 1-Click Upgrade module', async () => {
    const textResult = await moduleCatalogPage.installModule(page, moduleToInstall.name);

    await expect(textResult).to.contain(moduleCatalogPage.installMessageSuccessful(moduleToInstall.tag));
  });
});
