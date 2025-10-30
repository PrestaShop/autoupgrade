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
  boModuleManagerPage,
  boInstalledModulesPage,
  boModuleSelectionPage,
  boModuleManagerUninstalledModulesPage,
  boMarketplacePage,
  dataModules,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
 Install Update assistant module
 */
if (semver.lt(psVersion, '8.0.0')) {
  test.describe('Install update assistant module', () => {
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
    test('should login in BO', async () => {
      await boLoginPage.goTo(page, global.BO.URL);
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    // Steps to install module
    if (semver.lt(psVersion, '7.4.0')) {
      test('should go to \'Modules > Modules & Services\' page', async () => {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.modulesParentLink,
          boDashboardPage.moduleManagerLink,
        );
        await boModuleManagerPage.closeSfToolBar(page);

        const pageTitle = await boModuleSelectionPage.getPageTitle(page);
        expect(pageTitle).toContain(boModuleSelectionPage.pageTitle);
      });

      test(`should install the module '${dataModules.autoupgrade.name}'`, async () => {
        const successMessage = await boModuleSelectionPage.installModule(page, dataModules.autoupgrade.tag);
        expect(successMessage).toEqual(boModuleSelectionPage.installMessageSuccessful(dataModules.autoupgrade.tag));
      });
    } else if (semver.lt(psVersion, '7.5.0')) {
      test('should go to \'Modules > Modules & Services\' page', async () => {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.modulesParentLink,
          boDashboardPage.moduleManagerLink,
        );
        await boModuleManagerPage.closeSfToolBar(page);

        const pageTitle = await boInstalledModulesPage.getPageTitle(page);
        expect(pageTitle).toContain(boInstalledModulesPage.pageTitle);
      });

      test('should go to Selection page', async () => {
        await boInstalledModulesPage.goToSelectionPage(page);

        const pageTitle = await boModuleSelectionPage.getPageTitle(page);
        expect(pageTitle).toContain(boModuleSelectionPage.pageTitle);
      });

      test(`should install the module '${dataModules.autoupgrade.name}'`, async () => {
        const successMessage = await boModuleSelectionPage.installModule(page, dataModules.autoupgrade.tag);
        expect(successMessage).toEqual(boModuleSelectionPage.installMessageSuccessful(dataModules.autoupgrade.tag));
      });
    } else if (semver.lt(psVersion, '7.6.0')) {
      test('should go to \'Modules > Marketplace\' page', async () => {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.modulesParentLink,
          boDashboardPage.moduleCatalogueLink,
        );
        await boModuleManagerPage.closeSfToolBar(page);

        const pageTitle = await boMarketplacePage.getPageTitle(page);
        expect(pageTitle).toContain(boMarketplacePage.pageTitle);
      });

      test(`should install the module '${dataModules.autoupgrade.name}'`, async () => {
        const successMessage = await boMarketplacePage.installModule(page, dataModules.autoupgrade.tag);
        expect(successMessage).toEqual(boMarketplacePage.installMessageSuccessful(dataModules.autoupgrade.tag));
      });
    } else if (semver.lt(psVersion, '8.0.0')) {
      test('should go to \'Modules > Module Manager\' page', async () => {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.modulesParentLink,
          boDashboardPage.moduleManagerLink,
        );
        await boModuleManagerPage.closeSfToolBar(page);

        const pageTitle = await boModuleManagerPage.getPageTitle(page);
        expect(pageTitle).toContain(boModuleManagerPage.pageTitle);
      });

      test(`should install the module '${dataModules.autoupgrade.name}'`, async () => {
        await boModuleManagerUninstalledModulesPage.goToTabUninstalledModules(page);

        const isInstalled = await boModuleManagerUninstalledModulesPage.installModule(page, dataModules.autoupgrade.tag);
        expect(isInstalled).toBeTruthy();
      });
    }
  });
}
