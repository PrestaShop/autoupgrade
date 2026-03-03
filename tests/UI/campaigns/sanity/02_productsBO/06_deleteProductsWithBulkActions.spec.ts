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
  boProductsPage,
  boProductsCreatePage,
  // Import data
  FakerProduct,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Connect to the BO
  Go to Catalog > Products page
  Create 2 products
  Bulk delete the 2 created products from catalog page
 */
test.describe('BO - Catalog - Products : Delete products with bulk actions', async () => {
  let browserContext: BrowserContext;
  let page: Page;
  let numberOfProducts: number = 0;

  // Data to create first product
  const firstProductData: FakerProduct = new FakerProduct({
    name: 'toDelete1'.toUpperCase(),
    type: 'standard',
    quantity: 50,
    minimumQuantity: 1,
    status: true,
  });

  // Data to create second product
  const secondProductData: FakerProduct = new FakerProduct({
    name: 'toDelete2'.toUpperCase(),
    type: 'standard',
    quantity: 100,
    minimumQuantity: 1,
    status: true,
  });

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
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    // @ts-ignore
    if (!process.env.PS_VERSION.includes('classic')) {
      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    }
  });

  test('should go to \'Catalog > Products\' page', async () => {
    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.catalogParentLink,
      boDashboardPage.productsLink,
    );
    await boProductsPage.closeSfToolBar(page);

    const pageTitle = await boProductsPage.getPageTitle(page);
    expect(pageTitle).toContain(boProductsPage.pageTitle);
  });

  // @todo : https://github.com/PrestaShop/PrestaShop/issues/36097
  if (semver.lte(psVersion, '8.1.6') && semver.gte(psVersion, '7.3.0')) {
    test('should close the menu', async () => {
      await boDashboardPage.setSidebarCollapsed(page, true);

      const isSidebarCollapsed = await boDashboardPage.isSidebarCollapsed(page);
      expect(isSidebarCollapsed).toEqual(true);
    });
  }

  test.describe('Create first product', async () => {
    test('should reset filter and get number of products', async () => {
      numberOfProducts = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProducts).toBeGreaterThan(0);
    });

    test('should click on \'New product\' button', async () => {
      const isVisible = await boProductsPage.clickOnNewProductButton(page);
      expect(isVisible).toEqual(true);
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should choose \'Standard product\'', async () => {
        await boProductsPage.selectProductType(page, firstProductData.type);
        await boProductsPage.clickOnAddNewProduct(page);

        const pageTitle = await boProductsCreatePage.getPageTitle(page);
        expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
      });
    }

    test('should create standard product', async () => {
      await boProductsCreatePage.closeSfToolBar(page);

      const createProductMessage = await boProductsCreatePage.setProduct(page, firstProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Create second product', async () => {
    test('should click on \'New product\' button and check new product modal', async () => {
      const isVisible = await boProductsCreatePage.clickOnNewProductButton(page);
      expect(isVisible).toEqual(true);
    });

    test('should create product', async () => {
      if (semver.gte(psVersion, '8.1.0')) {
        await boProductsCreatePage.chooseProductType(page, secondProductData.type);
      }
      const createProductMessage = await boProductsCreatePage.setProduct(page, secondProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Bulk delete created products', async () => {
    test('should click on \'Go to catalog\' button', async () => {
      await boProductsCreatePage.goToCatalogPage(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);
    });

    test('should filter list by \'Name\' and check result', async () => {
      await boProductsPage.filterProducts(page, 'product_name', 'toDelete', 'input');

      const numberOfProductsAfterFilter = await boProductsPage.getNumberOfProductsFromList(page);
      expect(numberOfProductsAfterFilter).toEqual(2);

      const textColumn = await boProductsPage.getTextColumn(page, 'product_name', 1);
      expect(textColumn).toContain('TODELETE');
    });

    test('should select the 2 products', async () => {
      const isBulkDeleteButtonEnabled = await boProductsPage.bulkSelectProducts(page);
      expect(isBulkDeleteButtonEnabled).toEqual(true);
    });

    test('should click on bulk actions button', async () => {
      const textMessage = await boProductsPage.clickOnBulkActionsProducts(page, 'delete');

      if (semver.gte(psVersion, '8.1.0')) {
        expect(textMessage).toEqual('Deleting 2 products');
      } else {
        expect(textMessage).toEqual('These products will be deleted for good. Please confirm.');
      }
    });

    test('should bulk delete products', async () => {
      if (semver.gte(psVersion, '8.1.0')) {
        const textMessage = await boProductsPage.bulkActionsProduct(page, 'delete');
        expect(textMessage).toEqual('Deleting 2 / 2 products');
      } else {
        const textMessage = await boProductsPage.bulkActionsProduct(page, 'deletion');
        expect(textMessage).toEqual('Product(s) successfully deleted.');
      }
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should close progress modal', async () => {
        const isModalNotVisible = await boProductsPage.closeBulkActionsProgressModal(page, 'delete');
        expect(isModalNotVisible).toEqual(true);
      });
    }

    test('should reset filter', async () => {
      const numberOfProductsAfterReset = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProductsAfterReset).toEqual(numberOfProducts);
    });
  });
});
