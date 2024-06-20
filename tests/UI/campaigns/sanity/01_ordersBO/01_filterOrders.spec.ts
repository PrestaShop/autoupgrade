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

const baseContext: string = 'sanity_ordersBO_filterOrders';

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
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'loginBO', baseContext);

    await boLoginPage.goTo(page, global.BO.URL);
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).toContain(boDashboardPage.pageTitle);
  });

  test('should go to the \'Orders > Orders\' page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToOrdersPage', baseContext);

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
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'resetFilters1', baseContext);

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
        identifier: 'filterReference', filterType: 'input', filterBy: 'reference', filterValue: dataOrders.order_2.reference,
      },
    },
    {
      args: {
        identifier: 'filterOsName', filterType: 'select', filterBy: 'osname', filterValue: dataOrderStatuses.paymentError.name,
      },
    },
  ];

  tests.forEach((tst) => {
    test(`should filter the Orders table by '${tst.args.filterBy}' and check the result`, async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', tst.args.identifier, baseContext);

      await boOrdersPage.filterOrders(
        page,
        tst.args.filterType,
        tst.args.filterBy,
        tst.args.filterValue.toString(),
      );

      const textColumn = await boOrdersPage.getTextColumn(page, tst.args.filterBy, 1);
      await expect(textColumn).toEqual(tst.args.filterValue.toString());
    });

    test(`should reset filter by '${tst.args.filterBy}'`, async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', `reset_${tst.args.identifier}`, baseContext);

      const numberOfOrdersAfterReset = await boOrdersPage.resetAndGetNumberOfLines(page);
      await expect(numberOfOrdersAfterReset).toEqual(numberOfOrders);
    });
  });

  // Logout from BO
  test('should log out from BO', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'logoutBO', baseContext);

    await boLoginPage.logoutBO(page);

    const pageTitle = await boLoginPage.getPageTitle(page);
    expect(pageTitle).toContain(boLoginPage.pageTitle);
  });
});
