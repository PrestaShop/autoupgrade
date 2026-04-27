/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
  Create product
  Delete product from catalog page
 */
test.describe('BO - Catalog - Products : Delete product', async () => {
  let browserContext: BrowserContext;
  let page: Page;
  let numberOfProducts: number = 0;

  // Data to create standard product
  const newProductData: FakerProduct = new FakerProduct({
    type: 'standard',
    quantity: 50,
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

  test.describe('Create product', async () => {
    test('should reset filter and get number of products', async () => {
      numberOfProducts = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProducts).toBeGreaterThan(0);
    });

    test('should click on \'New product\' button and check new product modal', async () => {
      const isVisible = await boProductsPage.clickOnNewProductButton(page);
      expect(isVisible).toEqual(true);
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should choose \'Standard product\'', async () => {
        await boProductsPage.selectProductType(page, newProductData.type);
        await boProductsPage.clickOnAddNewProduct(page);

        const pageTitle = await boProductsCreatePage.getPageTitle(page);
        expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
      });
    }

    test('should create standard product', async () => {
      const createProductMessage = await boProductsCreatePage.setProduct(page, newProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Delete product', async () => {
    test('should click on \'Go to catalog\' button', async () => {
      await boProductsCreatePage.goToCatalogPage(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);
    });

    test('should search for the created product', async () => {
      await boProductsPage.filterProducts(page, 'product_name', newProductData.name, 'input');

      const textColumn = await boProductsPage.getTextColumn(page, 'product_name', 1);
      expect(textColumn).toContain(newProductData.name);
    });

    test('should click on delete product button', async () => {
      const isModalVisible = await boProductsPage.clickOnDeleteProductButton(page, 1);
      expect(isModalVisible).toEqual(true);
    });

    test('should delete product', async () => {
      const textMessage = await boProductsPage.clickOnConfirmDialogButton(page);
      expect(textMessage).toEqual(boProductsPage.successfulDeleteMessage);
    });

    test('should reset filter', async () => {
      const numberOfProductsAfterReset = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProductsAfterReset).toEqual(numberOfProducts);
    });
  });
});
