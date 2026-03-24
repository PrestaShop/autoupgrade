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
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  modAutoupgradeBoMain,
  modAutoupgradeBoModal,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

/*
  Check update modal
 */
test.describe('Check Update modal', () => {
  let browserContext: BrowserContext;
  let page: Page;
  let isModalVisible: boolean = true;

  test.beforeAll(async ({browser}) => {
    browserContext = await browser.newContext();
    page = await browserContext.newPage();
  });
  test.afterAll(async () => {
    await page.close();
  });

  // Steps
  test('should login in BO', async () => {
    await boLoginPage.goTo(page, global.BO.URL);
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD, false);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).toContain(boDashboardPage.pageTitle);
  });

  test('should get if the update modal is visible', async () => {
    isModalVisible = await modAutoupgradeBoModal.isModalVisible(page);
  });

  test('should check the update link', async () => {
    const isLinkVisible = await modAutoupgradeBoModal.isReleaseNoteLinkVisible(page);
    if (isModalVisible && isLinkVisible) {
      const updateLink = await modAutoupgradeBoModal.getReleaseNoteLinkFromModal(page);
      expect(updateLink).toContain('https://build.prestashop-project.org/news');
    } else {
      test.skip();
    }
  });

  test('should click on the update link from the modal', async () => {
    const isLinkVisible = await modAutoupgradeBoModal.isReleaseNoteLinkVisible(page);
    if (isModalVisible && isLinkVisible) {
      page = await modAutoupgradeBoModal.openReleaseNoteFromTheModal(page);

      const pageTitle = await modAutoupgradeBoModal.getPageTitle(page);
      expect(pageTitle).toContain('PrestaShop');
      expect(pageTitle.toLowerCase()).toContain('available');

      page = await modAutoupgradeBoModal.closePage(browserContext, page, 0);
    } else {
      test.skip();
    }
  });

  test('should check the support link', async () => {
    if (isModalVisible) {
      const supportLink = await modAutoupgradeBoModal.getSupportLinkFromModal(page);
      expect(supportLink).toEqual('https://www.prestashop-project.org/support/');
    } else {
      test.skip();
    }
  });

  test('should click on Update and check the PS version', async () => {
    if (isModalVisible) {
      const version = await modAutoupgradeBoModal.getPSVersionFromTheModal(page);

      await modAutoupgradeBoModal.clickOnUpdateButton(page);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);

      const currentVersion = await modAutoupgradeBoMain.getCurrentPSAndPHPVersion(page);
      expect(currentVersion).not.toContain(version);
    } else {
      test.skip();
    }
  });
});
