/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import {
  // Import utils
  utilsTest,
  // Import FO pages
  foClassicHomePage,
  foClassicLoginPage,
  foClassicCategoryPage,
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

import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Order a product and check order confirmation
 */
test.describe('BO - Checkout : Order a product and check order confirmation', async () => {
  let browserContext: BrowserContext;
  let page: Page;
  let allProductsNumber: number = 0;

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

    const result = await foClassicHomePage.isHomePage(page);
    expect(result).toEqual(true);
  });

  test('should go to login page', async () => {
    await foClassicHomePage.goToLoginPage(page);

    const pageTitle = await foClassicLoginPage.getPageTitle(page);
    expect(pageTitle).toEqual(foClassicLoginPage.pageTitle);
  });

  test('should sign In in FO with default account', async () => {
    await foClassicLoginPage.customerLogin(page, dataCustomers.johnDoe);

    const connected = await foClassicHomePage.isCustomerConnected(page);
    expect(connected, 'Customer is not connected in FO').toEqual(true);
  });

  test('should go to home page', async () => {
    const isHomepage = await foClassicHomePage.isHomePage(page);

    if (!isHomepage) {
      await foClassicHomePage.goToHomePage(page);
    }

    const result = await foClassicHomePage.isHomePage(page);
    expect(result).toEqual(true);
  });

  test('should check and get the products number', async () => {
    await foClassicHomePage.goToAllProductsPage(page);

    allProductsNumber = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(allProductsNumber).toBeGreaterThan(0);
  });

  test('should quick view the first product', async () => {
    await foClassicHomePage.goToHomePage(page);
    await foClassicHomePage.quickViewProduct(page, 1);

    const isQuickViewModalVisible = await foClassicModalQuickViewPage.isQuickViewProductModalVisible(page);
    expect(isQuickViewModalVisible).toEqual(true);
  });

  test('should add first product to cart and Proceed to checkout', async () => {
    await foClassicModalQuickViewPage.addToCartByQuickView(page);
    await foClassicModalBlockCartPage.proceedToCheckout(page);

    const pageTitle = await foClassicCartPage.getPageTitle(page);
    expect(pageTitle).toEqual(foClassicCartPage.pageTitle);
  });

  test('should check the cart details', async () => {
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

  test('should proceed to checkout and check Step Address', async () => {
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
    const isStepAddressComplete = await foClassicCheckoutPage.goToDeliveryStep(page);
    expect(isStepAddressComplete, 'Step Address is not complete').toEqual(true);
  });

  test('should validate Step Delivery and go to Payment Step', async () => {
    const isStepDeliveryComplete = await foClassicCheckoutPage.goToPaymentStep(page);
    expect(isStepDeliveryComplete, 'Step Address is not complete').toEqual(true);
  });

  test('should Pay by bank wire and confirm order', async () => {
    if (semver.gte(psVersion, '7.1.0')) {
      await foClassicCheckoutPage.choosePaymentAndOrder(page, dataPaymentMethods.wirePayment.moduleName);
    } else {
      await foClassicCheckoutPage.choosePaymentAndOrder(page, '2');
    }

    const pageTitle = await foClassicCheckoutOrderConfirmationPage.getPageTitle(page);
    expect(pageTitle).toEqual(foClassicCheckoutOrderConfirmationPage.pageTitle);

    const cardTitle = await foClassicCheckoutOrderConfirmationPage.getOrderConfirmationCardTitle(page);
    expect(cardTitle).toContain(foClassicCheckoutOrderConfirmationPage.orderConfirmationCardTitle);
  });
});
