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
  boOrdersPage,
  // Import data
  dataOrders,
  dataOrderStatuses,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Connect to the BO
  Filter the Orders table
  Logout from the BO
 */
test.describe('BO - Orders - Orders : Filter the Orders table by ID, REFERENCE, STATUS', () => {
  let browserContext: BrowserContext;
  let page: Page;
  let numberOfOrders: number;

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

  test('should go to the \'Orders > Orders\' page', async () => {
    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.ordersParentLink,
      boDashboardPage.ordersLink,
    );
    await boOrdersPage.closeSfToolBar(page);

    const pageTitle = await boOrdersPage.getPageTitle(page);
    await expect(pageTitle).toContain(boOrdersPage.pageTitle);
  });

  test('should reset all filters and get number of orders', async () => {
    numberOfOrders = await boOrdersPage.resetAndGetNumberOfLines(page);
    await expect(numberOfOrders).toBeGreaterThan(0);
  });

  const tests = [
    {
      args: {
        identifier: 'filterId', filterType: 'input', filterBy: 'id_order', filterValue: dataOrders.order_4.id,
      },
    },
    {
      args: {
        identifier: 'filterReference',
        filterType: 'input',
        filterBy: 'reference',
        filterValue: dataOrders.order_2.reference,
      },
    },
    {
      args: {
        identifier: 'filterOsName',
        filterType: 'select',
        filterBy: 'osname',
        filterValue: dataOrderStatuses.paymentError.name,
      },
    },
  ];

  tests.forEach((tst, index: number) => {
    test(`should filter the Orders table by '${tst.args.filterBy}' and check the result`, async () => {
      if (semver.lte(psVersion, '7.6.9') && index === 2) {
        await boOrdersPage.filterOrders(
          page,
          tst.args.filterType,
          'os!id_order_state',
          tst.args.filterValue.toString(),
        );
      } else {
        await boOrdersPage.filterOrders(
          page,
          tst.args.filterType,
          tst.args.filterBy,
          tst.args.filterValue.toString(),
        );
      }

      const textColumn = await boOrdersPage.getTextColumn(page, tst.args.filterBy, 1);
      await expect(textColumn).toEqual(tst.args.filterValue.toString());
    });

    test(`should reset filter by '${tst.args.filterBy}'`, async () => {
      const numberOfOrdersAfterReset = await boOrdersPage.resetAndGetNumberOfLines(page);
      await expect(numberOfOrdersAfterReset).toEqual(numberOfOrders);
    });
  });

  // Logout from BO
  test('should log out from BO', async () => {
    await boLoginPage.logoutBO(page);

    const pageTitle = await boLoginPage.getPageTitle(page);
    expect(pageTitle).toContain(boLoginPage.pageTitle);
  });
});
