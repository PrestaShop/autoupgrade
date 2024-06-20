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

const baseContext: string = 'sanity_productsBO_deleteProduct';
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
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'loginBO', baseContext);

    await boLoginPage.goTo(page, global.BO.URL);
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).toContain(boDashboardPage.pageTitle);
  });

  test('should go to \'Catalog > Products\' page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToProductsPage', baseContext);

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
  if (semver.lte(psVersion, '8.1.6')) {
    test('should close the menu', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'closeMenu', baseContext);

      await boDashboardPage.setSidebarCollapsed(page, true);

      const isSidebarCollapsed = await boDashboardPage.isSidebarCollapsed(page);
      expect(isSidebarCollapsed).toEqual(true);
    });
  }

  test.describe('Create product', async () => {
    test('should reset filter and get number of products', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'getNumberOfProduct', baseContext);

      numberOfProducts = await boProductsPage.resetAndGetNumberOfLines(page);
      expect(numberOfProducts).toBeGreaterThan(0);
    });

    test('should click on \'New product\' button and check new product modal', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'clickOnNewProductButton', baseContext);

      const isVisible = await boProductsPage.clickOnNewProductButton(page);
      expect(isVisible).toEqual(true);
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should choose \'Standard product\'', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'chooseStandardProduct', baseContext);

        await boProductsPage.selectProductType(page, newProductData.type);
        await boProductsPage.clickOnAddNewProduct(page);

        const pageTitle = await boProductsCreatePage.getPageTitle(page);
        expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
      });
    }

    test('should create standard product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'createStandardProduct', baseContext);

      const createProductMessage = await boProductsCreatePage.setProduct(page, newProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });
  });

  test.describe('Delete product', async () => {
    test('should click on \'Go to catalog\' button', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToCatalogPage', baseContext);

      await boProductsCreatePage.goToCatalogPage(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);
    });

    test('should click on delete product button', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'clickOnDeleteProduct', baseContext);

      const isModalVisible = await boProductsPage.clickOnDeleteProductButton(page, 1);
      expect(isModalVisible).toEqual(true);
    });

    test('should delete product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'deleteProduct', baseContext);

      const textMessage = await boProductsPage.clickOnConfirmDialogButton(page);
      expect(textMessage).toEqual(boProductsPage.successfulDeleteMessage);
    });
  });
});
