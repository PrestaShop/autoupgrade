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

const baseContext: string = 'sanity_productsBO_deleteProductsWithBulkActions';

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

  test.describe('Create first product', async () => {
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

    test('should choose \'Standard product\' and go to new product page', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'chooseStandardProduct', baseContext);

      await boProductsPage.selectProductType(page, firstProductData.type);
      await boProductsPage.clickOnAddNewProduct(page);

      const pageTitle = await boProductsCreatePage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
    });

    test('should create standard product', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'createStandardProduct', baseContext);

      await boProductsCreatePage.closeSfToolBar(page);

      const createProductMessage = await boProductsCreatePage.setProduct(page, firstProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Create second product', async () => {
    test('should click on \'New product\' button and check new product modal', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'clickOnNewProductButton2', baseContext);

      const isModalVisible = await boProductsCreatePage.clickOnNewProductButton(page);
      expect(isModalVisible).toEqual(true);
    });

    test('should choose \'Standard product\' and go to new product page', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'chooseStandardProduct2', baseContext);

      await boProductsCreatePage.chooseProductType(page, secondProductData.type);
      await boProductsCreatePage.closeSfToolBar(page);

      const createProductMessage = await boProductsCreatePage.setProduct(page, secondProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Bulk delete created products', async () => {
    test('should click on \'Go to catalog\' button', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'goToCatalogPage', baseContext);

      await boProductsCreatePage.goToCatalogPage(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);
    });

    test('should filter list by \'Name\' and check result', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'filterListByReference', baseContext);

      await boProductsPage.filterProducts(page, 'product_name', 'toDelete', 'input');

      const numberOfProductsAfterFilter = await boProductsPage.getNumberOfProductsFromList(page);
      expect(numberOfProductsAfterFilter).toEqual(2);

      const textColumn = await boProductsPage.getTextColumn(page, 'product_name', 1);
      expect(textColumn).toContain('TODELETE');
    });

    test('should select the 2 products', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'clickOnDeleteProduct', baseContext);

      const isBulkDeleteButtonEnabled: boolean = await boProductsPage.bulkSelectProducts(page);
      expect(isBulkDeleteButtonEnabled).toEqual(true);
    });

    test('should click on bulk actions button', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'clickOnBulkDeleteButton', baseContext);

      const textMessage = await boProductsPage.clickOnBulkActionsProducts(page, 'delete');
      expect(textMessage).toEqual('Deleting 2 products');
    });

    test('should bulk delete products', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'bulkDeleteProduct', baseContext);

      const textMessage = await boProductsPage.bulkActionsProduct(page, 'delete');
      expect(textMessage).toEqual('Deleting 2 / 2 products');
    });

    test('should close progress modal', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'closeProgressModal', baseContext);

      const isModalNotVisible = await boProductsPage.closeBulkActionsProgressModal(page, 'delete');
      expect(isModalNotVisible).toEqual(true);
    });

    test('should reset filter', async () => {
      await testContext.addContextItem(test.info(), 'testIdentifier', 'resetFilter', baseContext);

      const numberOfProductsAfterReset = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProductsAfterReset).toEqual(numberOfProducts);
    });
  });
});
