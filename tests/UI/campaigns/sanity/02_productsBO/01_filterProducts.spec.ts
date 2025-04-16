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
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boProductsPage,
  boNewExperimentalFeaturesPage,
  // Import data
  dataProducts,
  dataCategories,
} from '@prestashop-core/ui-testing';

import {
  test, expect,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Connect to the BO
  Go to Catalog > Products page
  Filter products table by ID, Name, Reference, Category, Price, Quantity and Status
  Logout from the BO
 */
test('BO - Catalog - Products : Filter the products table by ID, Name, Reference, Category, Price, Quantity and Status',
  async ({page}) => {
    let numberOfProducts: number = 0;
    let isProductPageV1: boolean = false;

    // Steps
    test.step('should login in BO', async () => {
      await boLoginPage.goTo(page, global.BO.URL);
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test.step('should go to \'Catalog > Products\' page', async () => {
      await boDashboardPage.goToSubMenu(
        page,
        boDashboardPage.catalogParentLink,
        boDashboardPage.productsLink,
      );
      await boProductsPage.closeSfToolBar(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);

      isProductPageV1 = !await boProductsPage.isProductPageV2(page);
    });

    test.step('should go to \'Advanced Parameters > New & Experimental Features\' page', async () => {
      if (semver.gte(psVersion, '8.1.0') && isProductPageV1) {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.advancedParametersLink,
          boDashboardPage.featureFlagLink,
        );
        await boNewExperimentalFeaturesPage.closeSfToolBar(page);

        const pageTitle = await boNewExperimentalFeaturesPage.getPageTitle(page);
        await expect(pageTitle).toContain(boNewExperimentalFeaturesPage.pageTitle);
      } else {
        test.skip();
      }
    });

    test.step('should enable product page V2', async () => {
      if (semver.gte(psVersion, '8.1.0') && isProductPageV1) {
        const successMessage = await boNewExperimentalFeaturesPage.setFeatureFlag(
          page, boNewExperimentalFeaturesPage.featureFlagProductPageV2, true);
        await expect(successMessage).toContain(boNewExperimentalFeaturesPage.successfulUpdateMessage);
      } else {
        test.skip();
      }
    });

    test.step('should go back to \'Catalog > Products\' page', async () => {
      if (semver.gte(psVersion, '8.1.0') && isProductPageV1) {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.catalogParentLink,
          boDashboardPage.productsLink,
        );
        await boProductsPage.closeSfToolBar(page);

        const pageTitle = await boProductsPage.getPageTitle(page);
        expect(pageTitle).toContain(boProductsPage.pageTitle);
      } else {
        test.skip();
      }
    });

    test.step('should check that no filter is applied by default', async () => {
      const isVisible = await boProductsPage.isResetButtonVisible(page);
      expect(isVisible, 'Reset button is visible!').toEqual(false);
    });

    test.step('should get the number of products', async () => {
      if (semver.lt(psVersion, '8.1.0') || isProductPageV1) {
        numberOfProducts = await boProductsPage.getNumberOfProductsFromList(page);
      } else {
        numberOfProducts = await boProductsPage.getNumberOfProductsFromHeader(page);
      }
      expect(numberOfProducts).toBeGreaterThan(0);
    });

    [
      {
        args: {
          identifier: 'filterIDMinMax',
          filterBy: 'id_product',
          filterValue: {min: 5, max: 10},
          // For PS version <= 1.7.2
          oldFilterValue: {min: 3, max: 7},
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterName',
          filterBy: 'product_name',
          filterValue: dataProducts.demo_14.name,
          // For PS version <= 1.7.2
          oldFilterValue: dataProducts.old_demo_4.name,
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterReference',
          filterBy: 'reference',
          filterValue: dataProducts.demo_14.reference,
          // For PS version <= 1.7.2
          oldFilterValue: dataProducts.old_demo_7.reference,
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterCategory',
          filterBy: 'category',
          filterValue: dataCategories.art.name,
          // For PS version <= 1.7.2
          oldFilterValue: dataProducts.old_demo_3.category,
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterPriceMinMax',
          filterBy: 'price',
          filterValue: {min: 5, max: 10},
          // For PS version <= 1.7.2
          oldFilterValue: {min: 20, max: 30},
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterQuantityMinMax',
          filterBy: 'quantity',
          filterValue: {min: 1300, max: 1500},
          // For PS version <= 1.7.2
          oldFilterValue: {min: 900, max: 1500},
          filterType: 'input',
        },
      },
      {
        args: {
          identifier: 'filterStatus',
          filterBy: 'active',
          filterValue: 'Yes',
          // For PS version <= 1.7.2
          oldFilterValue: 'Yes',
          filterType: 'select',
        },
      },
    ].forEach((tst) => {
      test.step(`should filter list by '${tst.args.filterBy}' and check result`, async () => {
        let filterValue:  string | {
          min: number
          max: number
        } = '';

        if (numberOfProducts > 7) {
          // For PS version > 1.7.2
          filterValue = tst.args.filterValue;
        } else {
          // For PS version <= 1.7.2
          filterValue = tst.args.oldFilterValue;
        }

        if (semver.lt(psVersion, '8.1.0') && tst.args.filterBy === 'active') {
          await boProductsPage.filterProducts(page, tst.args.filterBy, 'Active', tst.args.filterType);
        } else {
          await boProductsPage.filterProducts(page, tst.args.filterBy, filterValue, tst.args.filterType);
        }
        const numberOfProductsAfterFilter = await boProductsPage.getNumberOfProductsFromList(page);

        if (tst.args.filterBy === 'active') {
          expect(numberOfProductsAfterFilter).toBeGreaterThan(0);
        } else {
          expect(numberOfProductsAfterFilter).toBeLessThan(numberOfProducts);
        }

        for (let i = 1; i <= numberOfProductsAfterFilter; i++) {
          const textColumn = await boProductsPage.getTextColumn(page, tst.args.filterBy, i);

          if (typeof filterValue !== 'string') {
            expect(textColumn).toBeGreaterThanOrEqual(filterValue.min);
            expect(textColumn).toBeLessThanOrEqual(filterValue.max);
          } else if (tst.args.filterBy === 'active') {
            expect(textColumn).toEqual(true);
          } else {
            expect(textColumn).toContain(filterValue);
          }
        }
      });

      test.step(`should reset filter by '${tst.args.filterBy}'`, async () => {
        const numberOfProductsAfterReset = await boProductsPage.resetAndGetNumberOfLines(page);
        expect(numberOfProductsAfterReset).toEqual(numberOfProducts);
      });
    });
  });