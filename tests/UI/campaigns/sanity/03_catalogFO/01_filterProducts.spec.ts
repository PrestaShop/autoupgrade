import {
  // Import utils
  utilsTest,
  // Import FO pages
  foClassicHomePage,
  foClassicCategoryPage,
  // Import data
  dataCategories,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

const baseContext: string = 'sanity_catalogFO_filterProducts';

/*
  Open the FO home page
  Get the product number
  Filter products by a category
  Filter products by a subcategory
 */
test.describe('FO - Catalog : Filter Products by categories in Home page', async () => {
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
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'goToShopFO', baseContext);

    await foClassicHomePage.goTo(page, global.FO.URL);

    const result = await foClassicHomePage.isHomePage(page);
    expect(result).toEqual(true);
  });

  test('should check and get the products number', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'checkNumberOfProducts', baseContext);

    await foClassicHomePage.goToAllProductsPage(page);

    allProductsNumber = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(allProductsNumber).toBeGreaterThan(0);
  });

  test('should filter products by the category \'Accessories\' and check result', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'FilterProductByCategory', baseContext);

    await foClassicCategoryPage.goToCategory(page, dataCategories.accessories.id);

    const pageTitle = await foClassicCategoryPage.getPageTitle(page);
    expect(pageTitle).toEqual(dataCategories.accessories.name);

    const numberOfProducts = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(numberOfProducts).toBeLessThan(allProductsNumber);
  });

  test('should filter products by the subcategory \'Stationery\' and check result', async () => {
    await utilsTest.addContextItem(test.info(), 'testIdentifier', 'FilterProductBySubCategory', baseContext);

    await foClassicCategoryPage.reloadPage(page);
    await foClassicCategoryPage.goToSubCategory(page, dataCategories.accessories.id, dataCategories.stationery.id);

    const numberOfProducts = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(numberOfProducts).toBeLessThan(allProductsNumber);
  });
});
