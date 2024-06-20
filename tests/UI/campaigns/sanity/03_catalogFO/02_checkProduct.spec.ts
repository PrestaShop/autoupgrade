import {
  // Import utils
  utilsTest,
  // Import FO pages
  foClassicHomePage,
  foClassicProductPage,
  // Import data
  dataProducts,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

const baseContext: string = 'sanity_catalogFO_checkProduct';

/*
  Open the FO home page
  Check the first product page
 */
test.describe('FO - Catalog : Check the Product page', async () => {
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

  test('should go to the first product page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToProductPage', baseContext);

    await foClassicHomePage.goToProductPage(page, 1);

    const pageTitle = await foClassicProductPage.getPageTitle(page);
    expect(pageTitle).toContain(dataProducts.demo_1.name);
  });

  test('should check the product page', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkProductPage', baseContext);

    const result = await foClassicProductPage.getProductInformation(page);
    await Promise.all([
      expect(result.name).toEqual(dataProducts.demo_1.name),
      expect(result.price).toEqual(dataProducts.demo_1.finalPrice),
      expect(result.description).toContain(dataProducts.demo_1.description),
    ]);
  });
});
