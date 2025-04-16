import {expect, test, test as base} from '@playwright/test';
import {boDashboardPage, boLoginPage, boProductsPage, utilsTest} from "@prestashop-core/ui-testing";
import semver from "semver";

export type ProductsFixture = {
  goTo: string;
};

export const productsPage = base.extend<ProductsFixture>({
  goTo: [ async ({ page }, use) => {
    // let productPageURL: string;
    const psVersion = utilsTest.getPSVersion();


    await test.step('should login in BO', async () => {
      await boLoginPage.goTo(page, global.BO.URL);
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    await test.step('should go to \'Catalog > Products\' page', async () => {
      await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.catalogParentLink,
          boDashboardPage.productsLink,
      );
      await boProductsPage.closeSfToolBar(page);

      const pageTitle = await boProductsPage.getPageTitle(page);
      expect(pageTitle).toContain(boProductsPage.pageTitle);

      // productPageURL = await boProductsPage.getCurrentURL(page);
      // if (productPageURL.split('products-v2').length - 1) {
      //   isProductPageV1 = false;
      // }
    });

    // @todo : https://github.com/PrestaShop/PrestaShop/issues/36097
    if (semver.lte(psVersion, '8.1.6') && semver.gte(psVersion, '7.3.0')) {
      await test.step('should close the menu', async () => {
        await boDashboardPage.setSidebarCollapsed(page, true);

        const isSidebarCollapsed = await boDashboardPage.isSidebarCollapsed(page);
        expect(isSidebarCollapsed).toEqual(true);
      });
    }

    await use('');
  },
    { auto: true },
      ]
});