/*
import {
  // Import utils
  testContext,
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

const baseContext: string = 'sanity_productsBO_deleteProduct';

/!*
  Connect to the BO
  Go to Catalog > Products page
  Create product
  Delete product from catalog page
 *!/
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

  test.describe('Create product', async () => {
    test('should reset filter and get number of products', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'getNumberOfProduct', baseContext);

      numberOfProducts = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProducts).toBeGreaterThan(0);
    });

    test('should click on \'New product\' button and check new product modal', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'clickOnNewProductButton', baseContext);

      const isModalVisible = await boProductsPage.clickOnNewProductButton(page);
      expect(isModalVisible).toEqual(true);
    });

    test('should choose \'Standard product\'', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'chooseStandardProduct', baseContext);

      await boProductsPage.selectProductType(page, newProductData.type);

      const pageTitle = await boProductsCreatePage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
    });

    test('should go to new product page', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'goToNewProductPage', baseContext);

      await boProductsPage.clickOnAddNewProduct(page);

      const pageTitle = await boProductsCreatePage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
    });

    test('should create standard product', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'createStandardProduct', baseContext);

      await boProductsCreatePage.closeSfToolBar(page);

      const createProductMessage = await boProductsCreatePage.setProduct(page, newProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Delete product', async () => {
    test('should click on \'Go to catalog\' button', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'goToCatalogPage', baseContext);

      await boProductsCreatePage.goToCatalogPage(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);
    });

    test('should filter list by \'Reference\' and check result', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'filterListByReference', baseContext);

      await boProductsPage.filterProducts(page, 'reference', newProductData.reference, 'input');

      const numberOfProductsAfterFilter = await boProductsPage.getNumberOfProductsFromList(page);
      expect(numberOfProductsAfterFilter).toEqual(1);

      const textColumn = await boProductsPage.getTextColumn(page, 'reference', 1);
      expect(textColumn).toEqual(newProductData.reference);
    });

    test('should click on delete product button', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'clickOnDeleteProduct', baseContext);

      const isModalVisible = await boProductsPage.clickOnDeleteProductButton(page, 1);
      expect(isModalVisible).toEqual(true);
    });

    test('should delete product', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'deleteProduct', baseContext);

      const textMessage = await boProductsPage.clickOnConfirmDialogButton(page);
      expect(textMessage).toEqual(boProductsPage.successfulDeleteMessage);
    });

    test('should reset filter', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'resetFilter', baseContext);

      const numberOfProductsAfterReset = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProductsAfterReset).toEqual(numberOfProducts);
    });
  });
});
*/
