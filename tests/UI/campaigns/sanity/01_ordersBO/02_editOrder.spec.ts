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
  boOrdersPage,
  boOrdersViewBlockProductsPage,
  boOrdersViewBasePage,
  // Import data
  dataOrderStatuses,
} from '@prestashop-core/ui-testing';

import setupSmtpConfigTest from 'commonTests/smtp';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

/*
Connect to the BO
Edit the first order
Logout from the BO
*/
test.describe('BO - Orders - Orders : Edit Order BO', async () => {
  let browserContext: BrowserContext;
  let page: Page;

  // before and after functions
  test.beforeAll(async ({browser}) => {
    browserContext = await browser.newContext();
    page = await browserContext.newPage();
  });
  test.afterAll(async () => {
    await page.close();
  });

  // Pre-Condition: Setup config SMTP
  setupSmtpConfigTest();

  // Steps
  test('should login in BO', async () => {
    await boLoginPage.goTo(page, global.BO.URL);
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).toContain(boDashboardPage.pageTitle);
  });

  test('should go to the \'Orders > Orders\' page', async () => {
    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.ordersParentLink,
      boDashboardPage.ordersLink,
    );
    await boOrdersPage.closeSfToolBar(page);

    const pageTitle = await boOrdersPage.getPageTitle(page);
    expect(pageTitle).toContain(boOrdersPage.pageTitle);
  });

  test('should go to the first order page', async () => {
    await boOrdersPage.goToOrder(page, 1);

    const pageTitle = await boOrdersViewBlockProductsPage.getPageTitle(page);
    expect(pageTitle).toContain(boOrdersViewBlockProductsPage.pageTitle);
  });

  test('should modify the product quantity and check the validation', async () => {
    const newQuantity = await boOrdersViewBlockProductsPage.modifyProductQuantity(page, 1, 5);
    expect(newQuantity, 'Quantity was not updated').toEqual(5);
  });

  test('should modify the order status and check the validation', async () => {
    const orderStatus = await boOrdersViewBasePage.modifyOrderStatus(page, dataOrderStatuses.paymentAccepted.name);
    expect(orderStatus).toEqual(dataOrderStatuses.paymentAccepted.name);
  });

  // Logout from BO
  test('should log out from BO', async () => {
    await boLoginPage.logoutBO(page);

    const pageTitle = await boLoginPage.getPageTitle(page);
    expect(pageTitle).toContain(boLoginPage.pageTitle);
  });
});
