import {
  // Import utils
  utilsTest,
  utilsCore,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boProductsPage,
  boProductsCreatePage,
  // Import FO pages
  foClassicProductPage,
  // Import data
  FakerProduct,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const baseContext: string = 'sanity_productsBO_CRUDStandardProduct';
const psVersion = utilsTest.getPSVersion();

/*
  Connect to the BO
  Go to Catalog > Products page
  Create/View/Update/Delete standard product
 */
test.describe('BO - Catalog - Products : CRUD standard product', async () => {
  let browserContext: BrowserContext;
  let page: Page;
  let productPageURL: string;
  let isProductPageV1: boolean = false;

  // Data to create standard product
  const newProductData: FakerProduct = new FakerProduct({
    type: 'standard',
    taxRule: 'No tax',
    tax: 0,
    quantity: 50,
    minimumQuantity: 1,
    status: true,
  });
  // Data to update standard product
  const updateProductData: FakerProduct = new FakerProduct({
    type: 'standard',
    taxRule: 'FR Taux réduit (10%)',
    tax: 10,
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

    productPageURL = await boProductsPage.getCurrentURL(page);
    if (productPageURL.split('products-v1').length - 1) {
      isProductPageV1 = true;
    }
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
    test('should click on \'New product\' button', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'clickOnNewProductButton', baseContext);

      const isVisible = await boProductsPage.clickOnNewProductButton(page);
      expect(isVisible).toEqual(true);
    });

    if (semver.gte(psVersion, '8.1.0') || isProductPageV1) {
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

    if (semver.gte(psVersion, '8.1.0') || isProductPageV1) {
      test('should check that the save button is changed to \'Save and publish\'', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkSaveButton', baseContext);

        const saveButtonName = await boProductsCreatePage.getSaveButtonName(page);
        expect(saveButtonName).toEqual('Save and publish');
      });
    }

    test('should preview product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'previewProduct', baseContext);

      // Click on preview button
      page = await boProductsCreatePage.previewProduct(page);

      await foClassicProductPage.changeLanguage(page, 'en');

      if (semver.gte(psVersion, '8.1.0') || isProductPageV1) {
        const pageTitle = await foClassicProductPage.getPageTitle(page);
        expect(pageTitle).toContain(newProductData.name);
      }
    });

    test('should check all product information', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkProductInformation', baseContext);

      const result = await foClassicProductPage.getProductInformation(page);
      await Promise.all([
        expect(result.name).toEqual(newProductData.name),
        expect(result.price).toEqual(newProductData.price),
        expect(result.summary).toEqual(newProductData.summary),
        expect(result.description).toEqual(newProductData.description),
      ]);
    });
  });

  test.describe('Update product', async () => {
    test('should go back to BO to update product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goBackToBO', baseContext);

      // Go back to BO
      page = await foClassicProductPage.closePage(browserContext, page, 0);

      const pageTitle = await boProductsCreatePage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
    });

    test('should update the created product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'updateProduct', baseContext);

      const createProductMessage = await boProductsCreatePage.setProduct(page, updateProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });

    test('should preview product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'previewUpdatedProduct', baseContext);

      // Click on preview button
      page = await boProductsCreatePage.previewProduct(page);

      await foClassicProductPage.changeLanguage(page, 'en');

      const pageTitle = await foClassicProductPage.getPageTitle(page);
      expect(pageTitle).toContain(updateProductData.name);
    });

    test('should check all product information', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkUpdatedProductInformation', baseContext);

      const taxValue = await utilsCore.percentage(updateProductData.priceTaxExcluded, 10);

      const result = await foClassicProductPage.getProductInformation(page);
      await Promise.all([
        expect(result.name).toEqual(updateProductData.name),
        expect(result.price).toEqual(updateProductData.priceTaxExcluded + taxValue),
        expect(result.description).toEqual(updateProductData.description),
      ]);
    });
  });

  test.describe('Delete product', async () => {
    test('should go back to BO to delete product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goBackToBOToDeleteProduct', baseContext);

      // Go back to BO
      page = await foClassicProductPage.closePage(browserContext, page, 0);

      const pageTitle = await boProductsCreatePage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
    });

    test('should delete product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'deleteProduct', baseContext);

      const createProductMessage = await boProductsCreatePage.deleteProduct(page);
      expect(createProductMessage).toEqual(boProductsPage.successfulDeleteMessage);
    });
  });
});
