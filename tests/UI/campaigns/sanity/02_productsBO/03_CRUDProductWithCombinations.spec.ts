import {
  // Import utils
  utilsTest,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boProductsPage,
  boProductsCreatePage,
  boProductsCreateTabCombinationsPage,
  // Import FO pages
  foClassicProductPage,
  // Import data
  FakerProduct,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const baseContext: string = 'sanity_productsBO_CRUDProductWithCombinations';
const psVersion = utilsTest.getPSVersion();

/*
  Connect to the BO
  Go to Catalog > Products page
  Create/View/Update/Delete product with combinations
 */
test.describe('BO - Catalog - Products : CRUD product with combinations', async () => {
  let browserContext: BrowserContext;
  let page: Page;

  // Data to create product with combinations
  const newProductData: FakerProduct = new FakerProduct({
    type: 'combinations',
    taxRule: 'No tax',
    tax: 0,
    quantity: 50,
    minimumQuantity: 1,
    status: true,
    attributes: [
      {
        name: 'color',
        values: ['White', 'Black'],
      },
      {
        name: 'size',
        values: ['M'],
      },
    ],
  });
    // Data to update product with combinations
  const updateProductData: FakerProduct = new FakerProduct({
    type: 'combinations',
    taxRule: 'No tax',
    tax: 0,
    quantity: 100,
    minimumQuantity: 1,
    status: true,
    attributes: [
      {
        name: 'color',
        values: ['Gray', 'Grey','Taupe', 'Red'],
      },
      {
        name: 'size',
        values: ['L', 'XL'],
      },
    ],
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
    test('should click on \'New product\' button and check new product modal', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'clickOnNewProductButton', baseContext);

      const isModalVisible = await boProductsPage.clickOnNewProductButton(page);
      expect(isModalVisible).toEqual(true);
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should choose \'Product with combinations\'', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'chooseProductWithCombinations', baseContext);

        await boProductsPage.selectProductType(page, newProductData.type);
        await boProductsPage.clickOnAddNewProduct(page);

        const pageTitle = await boProductsCreatePage.getPageTitle(page);
        expect(pageTitle).toContain(boProductsCreatePage.pageTitle);
      });
    }

    test('should create product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'createProduct', baseContext);

      const createProductMessage = await boProductsCreatePage.setProduct(page, newProductData);
      expect(createProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should check that the save button is changed to \'Save and publish\'', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkSaveButton', baseContext);

        const saveButtonName = await boProductsCreatePage.getSaveButtonName(page);
        expect(saveButtonName).toEqual('Save and publish');
      });
    }

    test('should create combinations and check generate combinations button', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'createCombinations', baseContext);

      const textResult = await boProductsCreateTabCombinationsPage.setProductAttributes(
        page,
        newProductData.attributes,
      );

      if (semver.gte(psVersion, '8.1.0')) {
        expect(textResult).toEqual('Generate 2 combinations');
      } else {
        expect(textResult).toEqual(boProductsCreatePage.successfulUpdateMessage);
      }
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should click on generate combinations button', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'generateCombinations', baseContext);

        const successMessage = await boProductsCreateTabCombinationsPage.generateCombinations(page);
        expect(successMessage).toEqual('Successfully generated 2 combinations.');
      });

      test('should check that combinations generation modal is closed', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'generateCombinationsModalIsClosed', baseContext);

        const isModalClosed = await boProductsCreateTabCombinationsPage.generateCombinationModalIsClosed(page);
        expect(isModalClosed).toEqual(true);
      });
    }

    test('should save the created product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'saveProduct', baseContext);

      const updateProductMessage = await boProductsCreatePage.saveProduct(page);
      expect(updateProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });

    test('should preview product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'previewProduct', baseContext);

      // Click on preview button
      page = await boProductsCreatePage.previewProduct(page);

      await foClassicProductPage.changeLanguage(page, 'en');

      const pageTitle = await foClassicProductPage.getPageTitle(page);
      expect(pageTitle).toContain(newProductData.name);
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

      const productAttributes = await foClassicProductPage.getProductAttributes(page);
      await Promise.all([
        // color
        expect(productAttributes[0].value).toEqual(newProductData.attributes[1].values.join(' ')),
        // size
        expect(productAttributes[1].value).toEqual(newProductData.attributes[0].values.join(' ')),
      ]);
    });
  });

  test.describe('update product', async () => {
    test('should go back to BO to edit product', async () => {
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

    test('should add combinations and check generate combinations button', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'addCombinations', baseContext);

      const textResult = await boProductsCreateTabCombinationsPage.setProductAttributes(
        page,
        updateProductData.attributes,
      );

      if (semver.gte(psVersion, '8.1.0')) {
        expect(textResult).toEqual('Generate 6 combinations');
      } else {
        expect(textResult).toEqual(boProductsCreatePage.successfulUpdateMessage);
      }
    });

    if (semver.gte(psVersion, '8.1.0')) {
      test('should click on generate combinations button', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'generateCombinations2', baseContext);

        const successMessage = await boProductsCreateTabCombinationsPage.generateCombinations(page);
        expect(successMessage).toEqual('Successfully generated 6 combinations.');
      });

      test('should check that combinations generation modal is closed', async () => {
        await utilsTest.addContextItem(test.info(), 'testIdentifier', 'generateCombinationsModalIsClosed2', baseContext);

        const isModalClosed = await boProductsCreateTabCombinationsPage.generateCombinationModalIsClosed(page);
        expect(isModalClosed).toEqual(true);
      });
    }

    test('should save the Updated product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'saveProductUpdatedProduct', baseContext);

      const updateProductMessage = await boProductsCreatePage.saveProduct(page);
      expect(updateProductMessage).toEqual(boProductsCreatePage.successfulUpdateMessage);
    });

    test('should preview the updated product', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'previewUpdatedProduct', baseContext);

      // Click on preview button
      page = await boProductsCreatePage.previewProduct(page);

      await foClassicProductPage.changeLanguage(page, 'en');

      const pageTitle = await foClassicProductPage.getPageTitle(page);
      expect(pageTitle).toContain(updateProductData.name);
    });

    test('should check all product information', async () => {
      await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkUpdatedProductInformation', baseContext);

      const result = await foClassicProductPage.getProductInformation(page);
      await Promise.all([
        expect(result.name).toEqual(updateProductData.name),
        expect(result.price).toEqual(updateProductData.price),
        expect(result.description).toEqual(updateProductData.description),
      ]);

      const productAttributes = await foClassicProductPage.getProductAttributes(page);
      await Promise.all([
        expect(productAttributes[0].value).toEqual(
          `${newProductData.attributes[1].values.join(' ')} ${updateProductData.attributes[1].values.join(' ')}`),
        expect(productAttributes[1].value).toEqual(newProductData.attributes[0].values.join(' ')),
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
