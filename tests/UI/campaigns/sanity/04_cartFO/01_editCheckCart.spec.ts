import {
  // Import utils
  utilsTest,
  // Import FO pages
  foClassicHomePage,
  foClassicProductPage,
  foClassicCartPage,
  // Import data
  dataProducts,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

const baseContext: string = 'sanity_cartFO_editCheckCart';

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
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToShopFO', baseContext);

    await foClassicHomePage.goTo(page, global.FO.URL);

    const isHomePage = await foClassicHomePage.isHomePage(page);
    expect(isHomePage).toEqual(true);
  });

  test('should go to the first product page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToProductPage1', baseContext);

    await foClassicHomePage.goToProductPage(page, 1);

    const pageTitle = await foClassicProductPage.getPageTitle(page);
    expect(pageTitle).toContain(dataProducts.demo_1.name);
  });

  test('should add product to cart and check that the number of products is updated in cart header', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'addProductToCart1', baseContext);

    await foClassicProductPage.addProductToTheCart(page);
    // getNumberFromText is used to get the notifications number in the cart
    const notificationsNumber = await foClassicHomePage.getCartNotificationsNumber(page);
    expect(notificationsNumber).toEqual(1);
  });

  test('should go to the home page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToHomePage', baseContext);

    await foClassicHomePage.goToHomePage(page);

    const isHomePage = await foClassicHomePage.isHomePage(page);
    expect(isHomePage).toEqual(true);
  });

  test('should go to the second product page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToProductPage2', baseContext);

    await foClassicHomePage.goToProductPage(page, 2);

    const pageTitle = await foClassicProductPage.getPageTitle(page);
    expect(pageTitle).toContain(dataProducts.demo_3.name);
  });

  test('should add the second product to cart and check that the number of products is updated in cart header', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'addProductToCart2', baseContext);

    await foClassicProductPage.addProductToTheCart(page);

    // getNumberFromText is used to get the notifications number in the cart
    const notificationsNumber = await foClassicHomePage.getCartNotificationsNumber(page);
    expect(notificationsNumber).toEqual(2);
  });

  test('should check the first product details', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkProductDetail1', baseContext);

    const result = await foClassicCartPage.getProductDetail(page, 1);
    await Promise.all([
      expect(result.name).toEqual(dataProducts.demo_1.name),
      expect(result.price).toEqual(dataProducts.demo_1.finalPrice),
      expect(result.quantity).toEqual(1),
    ]);
  });

  test('should check the second product details', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkProductDetail2', baseContext);

    const result = await foClassicCartPage.getProductDetail(page, 2);
    await Promise.all([
      expect(result.name).toEqual(dataProducts.demo_3.name),
      expect(result.price).toEqual(dataProducts.demo_3.finalPrice),
      expect(result.quantity).toEqual(1),
    ]);
  });

  // @todo : https://github.com/PrestaShop/PrestaShop/issues/9779
  test.skip('should get the ATI price', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkTotalATI', baseContext);

    // getNumberFromText is used to get the price ATI
    totalATI = await foClassicCartPage.getATIPrice(page);
    expect(totalATI.toString()).toEqual((dataProducts.demo_3.finalPrice + dataProducts.demo_1.finalPrice)
      .toFixed(2));
  });

  test('should get the product number and check that is equal to 2', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkNumberOfProductsInCart', baseContext);

    totalATI = await foClassicCartPage.getATIPrice(page);

    // getNumberFromText is used to get the products number
    itemsNumber = await foClassicCartPage.getProductsNumber(page);
    expect(itemsNumber).toEqual(2);
  });

  test('should edit the quantity of the first product', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'editProductQuantity1', baseContext);

    await foClassicCartPage.editProductQuantity(page, 1, 3);

    // getNumberFromText is used to get the new price ATI
    const totalPrice = await foClassicCartPage.getATIPrice(page);
    expect(totalPrice).toBeGreaterThan(totalATI);

    // getNumberFromText is used to get the new products number
    const productsNumber = await foClassicCartPage.getProductsNumber(page);
    expect(productsNumber).toBeGreaterThan(itemsNumber);
  });

  test('should edit the quantity of the second product', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'editProductQuantity2', baseContext);

    await foClassicCartPage.editProductQuantity(page, 2, 2);

    // getNumberFromText is used to get the new price ATI
    const totalPrice = await foClassicCartPage.getATIPrice(page);
    expect(totalPrice).toBeGreaterThan(totalATI);

    // getNumberFromText is used to get the new products number
    const productsNumber = await foClassicCartPage.getCartNotificationsNumber(page);
    expect(productsNumber).toBeGreaterThan(itemsNumber);
  });
});
