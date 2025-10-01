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
  modAutoupgradeBoMain,
  modAutoupgradeBoModal,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

if (semver.gte(psVersion, '8.0.0')) {
  test.describe('Test modal of notification Update Assistant', () => {
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
    test('should login in BO', async () => {
      await page.goto(global.BO.URL, {timeout: 50000});
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test('should check the update link', async () => {
      const updateLink = await modAutoupgradeBoModal.getUpdateLinkFromModal(page);
      expect(updateLink).toContain('https://build.prestashop-project.org/news');
    });

    test('should click on the update link from the modal', async () => {
      page = await modAutoupgradeBoModal.openUpdateLinkFromTheModal(page);

      const pageTitle = await modAutoupgradeBoModal.getPageTitle(page);
      expect(pageTitle).toContain('PrestaShop');
      expect(pageTitle).toContain('available');

      page = await modAutoupgradeBoModal.closePage(browserContext, page, 0);
    });

    test('should check the support link', async () => {
      const supportLink = await modAutoupgradeBoModal.getSupportLinkFromModal(page);
      expect(supportLink).toEqual('https://www.prestashop-project.org/support/');
    });

    test('should click on Update and check the PS version', async () => {
      const version = await modAutoupgradeBoModal.getPSVersionFromTheModal(page);

      await modAutoupgradeBoModal.clickOnUpdateButton(page);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);

      const currentVersion = await modAutoupgradeBoMain.getCurrentPSAndPHPVersion(page);
      expect(currentVersion).not.toContain(version);
    });
  });
}
