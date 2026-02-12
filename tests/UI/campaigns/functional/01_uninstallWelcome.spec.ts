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
  boModuleManagerAlertsPage,
  modAutoupgradeBoModal,
  dataModules,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Uninstall welcome module
 */
if (semver.lt(psVersion, '8.0.0')) {
  test.describe('Uninstall welcome module', () => {
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

    test('should close update notification dialog', async () => {
      const isDialogNotVisible = await modAutoupgradeBoModal.closeDialogUpdateNotification(page);
      expect(isDialogNotVisible).toEqual(true);
    });

    // Steps to go to module configuration page
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

      test('should go to Installed modules page', async () => {
        await boModuleSelectionPage.goToInstalledModulesPage(page);

        const pageTitle = await boInstalledModulesPage.getPageTitle(page);
        expect(pageTitle).toContain(boInstalledModulesPage.pageTitle);
      });

      test(`should search the module '${dataModules.welcome.name}'`, async () => {
        const isModuleVisible = await boInstalledModulesPage.searchModule(page, dataModules.welcome);
        expect(isModuleVisible).toEqual(true);
      });

      test(`should uninstall the module '${dataModules.autoupgrade.name}'`, async () => {
        const successMessage = await boInstalledModulesPage.uninstallModule(page, dataModules.welcome.tag);
        expect(successMessage).toEqual(boModuleManagerAlertsPage.uninstallModuleSuccessMessage(dataModules.welcome.tag));
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

      test(`should search the module '${dataModules.welcome.name}'`, async () => {
        const isModuleVisible = await boInstalledModulesPage.searchModule(page, dataModules.welcome);
        expect(isModuleVisible).toEqual(true);
      });

      test(`should uninstall the module '${dataModules.welcome.name}'`, async () => {
        const successMessage = await boInstalledModulesPage.uninstallModule(page, dataModules.welcome.tag);
        expect(successMessage).toEqual(boModuleManagerAlertsPage.uninstallModuleSuccessMessage(dataModules.welcome.tag));
      });
    } else {
      test('should go to Module Manager page', async () => {
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.modulesParentLink,
          boDashboardPage.moduleManagerLink,
        );

        const pageTitle = await boModuleManagerPage.getPageTitle(page);
        expect(pageTitle).toContain(boModuleManagerPage.pageTitle);
      });

      test(`should search the module '${dataModules.welcome.name}'`, async () => {
        const isModuleVisible = await boModuleManagerPage.searchModule(page, dataModules.welcome);
        expect(isModuleVisible).toEqual(true);
      });

      test(`should uninstall the module '${dataModules.autoupgrade.name}'`, async () => {
        const successMessage = await boModuleManagerPage.setActionInModule(page, dataModules.welcome, 'uninstall');
        expect(successMessage).toEqual(boModuleManagerAlertsPage.uninstallModuleSuccessMessage(dataModules.welcome.tag));
      });
    }
  });
}
