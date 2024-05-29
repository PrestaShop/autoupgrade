import {
  // Import utils
  testContext,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boProductsPage,
  // Import data
  dataProducts,
  dataCategories,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

const baseContext: string = 'sanity_productsBO_filterProducts';

/*
  Connect to the BO
  Go to Catalog > Products page
  Filter products table by ID, Name, Reference, Category, Price, Quantity and Status
  Logout from the BO
 */
test.describe('BO - Catalog - Products : Filter the products table by ID, Name, Reference, Category, Price, Quantity and Status',
  async () => {
    let browserContext: BrowserContext;
    let page: Page;
    let numberOfProducts: number = 0;

    test.beforeAll(async ({browser}) => {
      browserContext = await browser.newContext();
      page = await browserContext.newPage();
    });
    test.afterAll(async () => {
      await page.close();
    });

    // Steps
    test('should login in BO', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'loginBO', baseContext);

      await boLoginPage.goTo(page, global.BO.URL);
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test('should go to \'Catalog > Products\' page', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'goToProductsPage', baseContext);

      await boDashboardPage.goToSubMenu(
        page,
        boDashboardPage.catalogParentLink,
        boDashboardPage.productsLink,
      );
      await boProductsPage.closeSfToolBar(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);
    });

    test('should check that no filter is applied by default', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'checkNoFilter', baseContext);

      const isVisible = await boProductsPage.isResetButtonVisible(page);
      expect(isVisible, 'Reset button is visible!').toEqual(false);
    });

    test('should get number of products', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'getNumberOfProduct', baseContext);

      numberOfProducts = await boProductsPage.getNumberOfProductsFromHeader(page);
      expect(numberOfProducts).toBeGreaterThan(0);
    });

    [
      {
        args: {
          identifier: 'filterIDMinMax',
          filterBy: 'id_product',
          filterValue: {min: 5, max: 10},
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterName',
          filterBy: 'product_name',
          filterValue: dataProducts.demo_14.name,
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterReference',
          filterBy: 'reference',
          filterValue: dataProducts.demo_1.reference,
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterCategory',
          filterBy: 'category',
          filterValue: dataCategories.women.name,
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterPriceMinMax',
          filterBy: 'price',
          filterValue: {min: 5, max: 10},
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterQuantityMinMax',
          filterBy: 'quantity',
          filterValue: {min: 100, max: 1000},
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterStatus',
          filterBy: 'active',
          filterValue: 'Yes',
          filterType: 'select',
        },
      },
    ].forEach((tst) => {
      test(`should filter list by '${tst.args.filterBy}' and check result`, async () => {
        await testContext.addContextItem(test.info(), 'testIdentifier', `${tst.args.identifier}`, baseContext);

        await boProductsPage.filterProducts(page, tst.args.filterBy, tst.args.filterValue, tst.args.filterType);
        const numberOfProductsAfterFilter = await boProductsPage.getNumberOfProductsFromList(page);

        if (tst.args.filterBy === 'active') {
          expect(numberOfProductsAfterFilter).toBeGreaterThan(0);
        } else {
          expect(numberOfProductsAfterFilter).toBeLessThan(numberOfProducts);
        }

        for (let i = 1; i <= numberOfProductsAfterFilter; i++) {
          const textColumn = await boProductsPage.getTextColumn(page, tst.args.filterBy, i);

          if (typeof tst.args.filterValue !== 'string') {
            expect(textColumn).toBeGreaterThanOrEqual(tst.args.filterValue.min);
            expect(textColumn).toBeLessThanOrEqual(tst.args.filterValue.max);
          } else if (tst.args.filterBy === 'active') {
            expect(textColumn).toEqual(true);
          } else {
            expect(textColumn).toContain(tst.args.filterValue);
          }
        }
      });

      test(`should reset filter by '${tst.args.filterBy}'`, async () => {
        await testContext.addContextItem(test.info(), 'testIdentifier', `resetFilter${tst.args.identifier}`, baseContext);

        const numberOfProductsAfterReset = await boProductsPage.resetAndGetNumberOfLines(page);
        expect(numberOfProductsAfterReset).toEqual(numberOfProducts);
      });
    });

    // Logout from BO
    test('should log out from BO', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'logoutBO', baseContext);

      await boLoginPage.logoutBO(page);

      const pageTitle = await boLoginPage.getPageTitle(page);
      expect(pageTitle).toContain(boLoginPage.pageTitle);
    });
  });
