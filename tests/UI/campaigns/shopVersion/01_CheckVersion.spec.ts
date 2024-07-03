import {
  // Import utils
  utilsTest,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

const baseContext: string = 'shopVersion_checkVersion';
const psVersion = utilsTest.getPSVersion();

/*
 Open BO
 Check new version lin login page
 Login
 Check new version in dashboard page
 */
test.describe('Check new shop version', () => {
  let browserContext: BrowserContext;
  let page: Page;

  test.beforeAll(async ({browser}) => {
    browserContext = await browser.newContext();
    page = await browserContext.newPage();
  });
  test.afterAll(async () => {
    await page.close();
  });

  // Steps
  test('should go to BO', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'openBO', baseContext);

    await boLoginPage.goTo(page, global.BO.URL);

    const pageTitle = await boLoginPage.getPageTitle(page);
    expect(pageTitle).toContain(boLoginPage.pageTitle);
  });

  test('should login in BO', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'loginBO', baseContext);

    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).toContain(boDashboardPage.pageTitle);
  });

  test(`should check that the new shop version is ${psVersion}`, async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkShopVersionInDashboard', baseContext);

    const shopVersion = await boDashboardPage.getShopVersion(page);
    expect(shopVersion).toContain(psVersion);
  });
});
