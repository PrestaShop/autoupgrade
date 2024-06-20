import {
  // Import utils
  utilsTest,
  // Import FO pages
  foClassicHomePage,
  foClassicLoginPage,
  foClassicCartPage,
  foClassicCheckoutPage,
  foClassicCheckoutOrderConfirmationPage,
  foClassicModalQuickViewPage,
  foClassicModalBlockCartPage,
  // Import data
  dataCustomers,
  dataProducts,
  dataPaymentMethods,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

const baseContext: string = 'sanity_checkoutFO_orderProduct';

/*
  Order a product and check order confirmation
 */
test.describe('BO - Checkout : Order a product and check order confirmation', async () => {
  let browserContext: BrowserContext;
  let page: Page;

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

    const result = await foClassicHomePage.isHomePage(page);
    expect(result).toEqual(true);
  });

  test('should go to login page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToLoginPage', baseContext);

    await foClassicHomePage.goToLoginPage(page);

    const pageTitle = await foClassicLoginPage.getPageTitle(page);
    expect(pageTitle).toEqual(foClassicLoginPage.pageTitle);
  });

  test('should sign In in FO with default account', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'loginFO', baseContext);

    await foClassicLoginPage.customerLogin(page, dataCustomers.johnDoe);

    const connected = await foClassicHomePage.isCustomerConnected(page);
    expect(connected, 'Customer is not connected in FO').toEqual(true);
  });

  test('should go to home page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToHomePage', baseContext);

    const isHomepage = await foClassicHomePage.isHomePage(page);

    if (!isHomepage) {
      await foClassicHomePage.goToHomePage(page);
    }

    const result = await foClassicHomePage.isHomePage(page);
    expect(result).toEqual(true);
  });

  test('should quick view the first product', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'quickViewFirstProduct', baseContext);

    await foClassicHomePage.quickViewProduct(page, 1);

    const isQuickViewModalVisible = await foClassicModalQuickViewPage.isQuickViewProductModalVisible(page);
    expect(isQuickViewModalVisible).toEqual(true);
  });

  test('should add first product to cart and Proceed to checkout', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'addProductToCart', baseContext);

    await foClassicModalQuickViewPage.addToCartByQuickView(page);
    await foClassicModalBlockCartPage.proceedToCheckout(page);

    const pageTitle = await foClassicCartPage.getPageTitle(page);
    expect(pageTitle).toEqual(foClassicCartPage.pageTitle);
  });

  test('should check the cart details', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkCartDetails', baseContext);

    const result = await foClassicCartPage.getProductDetail(page, 1);
    await Promise.all([
      expect(result.name).toEqual(dataProducts.demo_1.name),
      expect(result.price).toEqual(dataProducts.demo_1.finalPrice),
      expect(result.quantity).toEqual(1),
    ]);
  });

  test('should proceed to checkout and check Step Address', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkAddressStep', baseContext);

    await foClassicCartPage.clickOnProceedToCheckout(page);

    const isCheckoutPage = await foClassicCheckoutPage.isCheckoutPage(page);
    expect(isCheckoutPage, 'Browser is not in checkout Page').toEqual(true);

    const isStepPersonalInformationComplete = await foClassicCheckoutPage.isStepCompleted(
      page,
      foClassicCheckoutPage.personalInformationStepForm,
    );
    expect(isStepPersonalInformationComplete, 'Step Personal information is not complete').toEqual(true);
  });

  test('should validate Step Address and go to Delivery Step', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkDeliveryStep', baseContext);

    const isStepAddressComplete = await foClassicCheckoutPage.goToDeliveryStep(page);
    expect(isStepAddressComplete, 'Step Address is not complete').toEqual(true);
  });

  test('should validate Step Delivery and go to Payment Step', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToPaymentStep', baseContext);

    const isStepDeliveryComplete = await foClassicCheckoutPage.goToPaymentStep(page);
    expect(isStepDeliveryComplete, 'Step Address is not complete').toEqual(true);
  });

  test('should Pay by back wire and confirm order', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'confirmOrder', baseContext);

    await foClassicCheckoutPage.choosePaymentAndOrder(page, dataPaymentMethods.wirePayment.moduleName);

    const pageTitle = await foClassicCheckoutOrderConfirmationPage.getPageTitle(page);
    expect(pageTitle).toEqual(foClassicCheckoutOrderConfirmationPage.pageTitle);

    const cardTitle = await foClassicCheckoutOrderConfirmationPage.getOrderConfirmationCardTitle(page);
    expect(cardTitle).toContain(foClassicCheckoutOrderConfirmationPage.orderConfirmationCardTitle);
  });
});
