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
  boMaintenancePage,
  dataModules,
  modAutoupgradeBoMain,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';
import {exec} from 'child_process';

const psVersion = utilsTest.getPSVersion();

/*
  Upgrade using the local channel
 */
test.describe('Upgrade using the local channel', () => {
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
    const isDialogNotVisible = await boDashboardPage.closeDialogUpdateNotification(page);
    expect(isDialogNotVisible).toEqual(true);
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

  // Steps to go to module configuration page
  if (semver.lt(psVersion, '7.4.0')) {
    test('should go to Installed modules page', async () => {
      await boModuleSelectionPage.goToInstalledModulesPage(page);

      const pageTitle = await boInstalledModulesPage.getPageTitle(page);
      expect(pageTitle).toContain(boInstalledModulesPage.pageTitle);
    });

    test(`should search the module '${dataModules.autoupgrade.name}'`, async () => {
      const isModuleVisible = await boInstalledModulesPage.searchModule(page, dataModules.autoupgrade);
      expect(isModuleVisible).toEqual(true);
    });

    test(`should go to the configuration page of the module '${dataModules.autoupgrade.name}'`, async () => {
      await boInstalledModulesPage.goToModuleConfigurationPage(page, dataModules.autoupgrade.tag);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
    });
  } else if (semver.lt(psVersion, '7.5.0')) {
    test('should go to Modules and services page', async () => {
      await boDashboardPage.goToSubMenu(
        page,
        boDashboardPage.modulesParentLink,
        boDashboardPage.moduleManagerLink,
      );

      const pageTitle = await boInstalledModulesPage.getPageTitle(page);
      expect(pageTitle).toContain(boInstalledModulesPage.pageTitle);
    });

    test(`should search the module '${dataModules.autoupgrade.name}'`, async () => {
      const isModuleVisible = await boInstalledModulesPage.searchModule(page, dataModules.autoupgrade);
      expect(isModuleVisible).toEqual(true);
    });

    test(`should go to the configuration page of the module '${dataModules.autoupgrade.name}'`, async () => {
      await boInstalledModulesPage.goToModuleConfigurationPage(page, dataModules.autoupgrade.tag);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
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

    test(`should search the module '${dataModules.autoupgrade.name}'`, async () => {
      const isModuleVisible = await boModuleManagerPage.searchModule(page, dataModules.autoupgrade);
      expect(isModuleVisible).toEqual(true);
    });

    test(`should go to the configuration page of the module '${dataModules.autoupgrade.name}'`, async () => {
      await boModuleManagerPage.goToConfigurationPage(page, dataModules.autoupgrade.tag);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
    });
  }

  test('should click on update your store radio button then get started', async () => {
    const isStepContentVisible = await modAutoupgradeBoMain.updateYourStore(page);
    expect(isStepContentVisible).toEqual(true);

    const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
    expect(stepTitle).toEqual('Version choice');

    await exec('docker exec -t prestashop chmod -R 777 /var/www/html/modules');
  });

  test('should choose local archive', async () => {
    test.setTimeout(1000_000);

    const isRequirementBlockVisible = await modAutoupgradeBoMain.chooseLocalArchive(page, process.env.PS_VERSION_END!);
    expect(isRequirementBlockVisible).toEqual(true);
  });

  test('should go to maintenance page', async () => {
    page = await modAutoupgradeBoMain.goToMaintenancePage(page);

    const pageTitle = await boMaintenancePage.getPageTitle(page);
    expect(pageTitle).toContain(boMaintenancePage.pageTitle);
  });

  test('should disable the store', async () => {
    const result = await boMaintenancePage.changeShopStatus(page, false);
    expect(result).toContain(boMaintenancePage.successfulUpdateMessage);
  });

  test('should add maintenance IP', async () => {
    const result = await boMaintenancePage.addMyIpAddress(page);
    expect(result).toContain(boMaintenancePage.successfulUpdateMessage);
  });

  test('should close the page', async () => {
    page = await boMaintenancePage.closePage(browserContext, page, 0);

    const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
    expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
  });

  test('should check that all the requirements are OK', async () => {
    await exec('docker exec -t prestashop chmod -R 777 /var/www/html/modules');

    const isNextButtonEnabled = await modAutoupgradeBoMain.checkRequirements(page, 'archive');
    expect(isNextButtonEnabled).toEqual(true);
  });

  test('should check the new PS version', async () => {
    const newVersion = await modAutoupgradeBoMain.getNewPSVersion(page);
    expect(newVersion).not.toContain(`${psVersion} `);
  });

  test('should click on next button and check that the step title is \'Update options\'', async () => {
    await modAutoupgradeBoMain.goToNextStep(page);

    const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
    expect(stepTitle).toEqual('Update options');
  });

  test('should click on next button and check that the step title is \'Back up your store\'', async () => {
    await modAutoupgradeBoMain.goToNextStep(page);

    const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
    expect(stepTitle).toEqual('Back up your store');
  });

  test('should click on \'Launch backup\' button and check the modal', async () => {
    const isModalVisible = await modAutoupgradeBoMain.clickOnLaunchBackup(page);
    expect(isModalVisible).toEqual(true);
  });

  test('should click on cancel button and check that the modal is not visible', async () => {
    const isModalNotVisible = await modAutoupgradeBoMain.cancelBackup(page);
    expect(isModalNotVisible).toEqual(true);
  });

  test('should click on update without backup and confirm the modal', async () => {
    await modAutoupgradeBoMain.clickOnUpdateWithoutBackup(page);
    await page.waitForTimeout(2000);

    const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
    expect(stepTitle).toEqual('Update');
  });

  test('should wait until the end of the update ', async () => {
    test.setTimeout(5000_000);

    const successMessage = await modAutoupgradeBoMain.checkUpdateSuccess(page);
    expect(successMessage).toContain(modAutoupgradeBoMain.updateSuccessMessage);
    expect(successMessage).not.toContain(`${psVersion} `);
  });

  test('should check the title of the last step', async () => {
    const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
    expect(stepTitle).toEqual('Post-update checklist');

    await page.waitForTimeout(20000);
  });
});
