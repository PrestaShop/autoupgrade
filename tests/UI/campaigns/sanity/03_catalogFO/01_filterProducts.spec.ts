/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
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
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

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
    await foClassicHomePage.goTo(page, global.FO.URL);

    const result = await foClassicHomePage.isHomePage(page);
    expect(result).toEqual(true);
  });

  test('should check and get the products number', async () => {
    await foClassicHomePage.goToAllProductsPage(page);

    allProductsNumber = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(allProductsNumber).toBeGreaterThan(0);
  });

  if (semver.gte(psVersion, '7.3.0')) {
    test('should filter products by category and check result', async () => {
      if (allProductsNumber > 7) {
        await foClassicCategoryPage.goToCategory(page, dataCategories.accessories.id);

        const pageTitle = await foClassicCategoryPage.getPageTitle(page);
        expect(pageTitle).toEqual(dataCategories.accessories.name);

        const numberOfProducts = await foClassicCategoryPage.getNumberOfProducts(page);
        expect(numberOfProducts).toBeLessThan(allProductsNumber);
      } else {
        await foClassicCategoryPage.goToCategory(page, dataCategories.oldWomen.id);

        const pageTitle = await foClassicCategoryPage.getPageTitle(page);
        expect(pageTitle).toEqual(dataCategories.oldWomen.name);

        const numberOfProducts = await foClassicCategoryPage.getNumberOfProducts(page);
        expect(numberOfProducts).toEqual(allProductsNumber);
      }
    });
  }

  test('should filter products by subcategory and check result', async () => {
    await foClassicCategoryPage.reloadPage(page);
    // Based on the scenario, the mouse already points to a menu that must be opened in the next steps.
    // On PS 1.7.3.0, the menu does not react if the mouse hovers it while the JS is being initialized,
    // so we move it elsewhere.
    await page.mouse.move(0, 0);

    if (allProductsNumber > 7) {
      await foClassicCategoryPage.goToSubCategory(page, dataCategories.accessories.id, dataCategories.stationery.id);
    } else {
      await foClassicCategoryPage.goToSubCategory(page, dataCategories.oldWomen.id, dataCategories.eveningDresses.id);
    }
    const numberOfProducts = await foClassicCategoryPage.getNumberOfProducts(page);
    expect(numberOfProducts).toBeLessThan(allProductsNumber);
  });
});
