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
