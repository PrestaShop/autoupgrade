/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import {
  // Import utils
  utilsTest,
  // Import FO pages
  foClassicHomePage,
  foClassicCategoryPage,
  foClassicProductPage,
  foClassicCartPage,
  // Import data
  dataProducts,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Open the FO home page
  Add the first product to the cart
  Add the second product to the cart
  Check the cart
  Edit the cart and check it
 */
test.describe('FO - Cart : Check Cart in FO', async () => {
  let browserContext: BrowserContext;
  let page: Page;
  let allProductsNumber: number = 0;
  let totalATI: number = 0;
  let itemsNumber: number = 0;

  test.beforeAll(async ({browser}) => {
    browserContext = await browser.newContext();
    page = await browserContext.newPage();
  });
  test.afterAll(async () => {
    await page.close();
  });

  // Steps
  test('should open the shop page', async () => {
    await foClassicHomePage.goTo(page, global.FO.URL);

    const isHomePage = await foClassicHomePage.isHomePage(page);
    expect(isHomePage).toEqual(true);
  });

  test('should check and get the products number', async () => {
    await foClassicHomePage.goToAllProductsPage(page);

    allProductsNumber = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(allProductsNumber).toBeGreaterThan(0);
  });

  test('should go to the first product page', async () => {
    await foClassicHomePage.goToHomePage(page);
    await foClassicHomePage.goToProductPage(page, 1);

    const pageTitle = await foClassicProductPage.getPageTitle(page);

    if (allProductsNumber > 7) {
      expect(pageTitle).toContain(dataProducts.demo_1.name);
    } else {
      expect(pageTitle).toContain(dataProducts.old_demo_1.name);
    }
  });

  test('should add product to cart and check that the number of products is updated in cart header', async () => {
    await foClassicProductPage.addProductToTheCart(page);

    const notificationsNumber = await foClassicHomePage.getCartNotificationsNumber(page);
    expect(notificationsNumber).toEqual(1);
  });

  test('should go to the home page', async () => {
    await foClassicHomePage.goToHomePage(page);

    const isHomePage = await foClassicHomePage.isHomePage(page);
    expect(isHomePage).toEqual(true);
  });

  test('should go to the second product page', async () => {
    await foClassicHomePage.goToProductPage(page, 2);

    const pageTitle = await foClassicProductPage.getPageTitle(page);

    if (allProductsNumber > 7) {
      expect(pageTitle).toContain(dataProducts.demo_3.name);
    } else {
      expect(pageTitle).toContain(dataProducts.old_demo_2.name);
    }
  });

  test('should add the second product to cart and check that the number of products is updated in cart header', async () => {
    await foClassicProductPage.addProductToTheCart(page);

    const notificationsNumber = await foClassicHomePage.getCartNotificationsNumber(page);
    expect(notificationsNumber).toEqual(2);
  });

  test('should check the first product details', async () => {
    if (allProductsNumber > 7) {
      const result = await foClassicCartPage.getProductDetail(page, 1);
      await Promise.all([
        expect(result.name).toEqual(dataProducts.demo_1.name),
        expect(result.price).toEqual(dataProducts.demo_1.finalPrice),
        expect(result.quantity).toEqual(1),
      ]);
    } else {
      const productName = await foClassicCartPage.getProductName(page, 1);
      expect(productName).toEqual(dataProducts.old_demo_1.name);

      const productPrice = await foClassicCartPage.getProductPrice(page, 1);
      expect(productPrice).toEqual(dataProducts.old_demo_1.finalPrice);

      const productQuantity = await foClassicCartPage.getProductQuantity(page, 1);
      expect(productQuantity).toEqual(1);
    }
  });

  test('should check the second product details', async () => {
    if (allProductsNumber > 7) {
      const result = await foClassicCartPage.getProductDetail(page, 2);
      await Promise.all([
        expect(result.name).toEqual(dataProducts.demo_3.name),
        expect(result.price).toEqual(dataProducts.demo_3.finalPrice),
        expect(result.quantity).toEqual(1),
      ]);
    } else {
      const productName = await foClassicCartPage.getProductName(page, 2);
      expect(productName).toEqual(dataProducts.old_demo_2.name);

      const productPrice = await foClassicCartPage.getProductPrice(page, 2);
      expect(productPrice).toEqual(dataProducts.old_demo_2.finalPrice);

      const productQuantity = await foClassicCartPage.getProductQuantity(page, 2);
      expect(productQuantity).toEqual(1);
    }
  });

  // @todo : https://github.com/PrestaShop/PrestaShop/issues/9779
  test.skip('should get the ATI price', async () => {
    totalATI = await foClassicCartPage.getATIPrice(page);
    if (allProductsNumber > 7) {
      expect(totalATI.toString()).toEqual((dataProducts.demo_3.finalPrice + dataProducts.demo_1.finalPrice)
        .toFixed(2));
    } else {
      expect(totalATI.toString()).toEqual((dataProducts.old_demo_1.finalPrice + dataProducts.old_demo_2.finalPrice)
        .toFixed(2));
    }
  });

  test('should get the products number and check that is equal to 2', async () => {
    totalATI = await foClassicCartPage.getATIPrice(page);

    itemsNumber = await foClassicCartPage.getProductsNumber(page);
    expect(itemsNumber).toEqual(2);
  });

  test('should edit the quantity of the first product', async () => {
    await foClassicCartPage.editProductQuantity(page, 1, 3);

    const totalPrice = await foClassicCartPage.getATIPrice(page);
    expect(totalPrice).toBeGreaterThan(totalATI);

    const productsNumber = await foClassicCartPage.getProductsNumber(page);
    expect(productsNumber).toBeGreaterThan(itemsNumber);
  });

  test('should edit the quantity of the second product', async () => {
    await foClassicCartPage.editProductQuantity(page, 2, 2);

    const totalPrice = await foClassicCartPage.getATIPrice(page);
    expect(totalPrice).toBeGreaterThan(totalATI);

    let productsNumber: number = 0;

    if (semver.gte(psVersion, '7.8.0')) {
      productsNumber = await foClassicCartPage.getCartNotificationsNumber(page);
    } else {
      productsNumber = await foClassicCartPage.getProductsNumber(page);
    }
    expect(productsNumber).toBeGreaterThan(itemsNumber);
  });
});
