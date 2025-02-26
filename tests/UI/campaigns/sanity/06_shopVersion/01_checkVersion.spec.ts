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
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
 Open BO
 Check new version in login page for PS version < 1.7.4
 Login
 Check new version in dashboard page for PS version >= 1.7.4
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
    await boLoginPage.goTo(page, global.BO.URL);

    const pageTitle = await boLoginPage.getPageTitle(page);
    expect(pageTitle).toContain(boLoginPage.pageTitle);
  });

  if (semver.lt(psVersion, '7.4.0')) {
    test(`should check that the shop version is ${psVersion}`, async () => {
      const shopVersion = await boLoginPage.getShopVersion(page);
      expect(shopVersion).toContain(psVersion);
    });
  }

  if (semver.gte(psVersion, '7.4.0')) {
    test('should login in BO', async () => {
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test(`should check that the new shop version is ${psVersion}`, async () => {
      const shopVersion = await boDashboardPage.getShopVersion(page);
      expect(shopVersion).toContain(psVersion);
    });
  }
});
