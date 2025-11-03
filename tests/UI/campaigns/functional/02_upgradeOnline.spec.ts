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
  boMaintenancePage,
  dataModules,
  modAutoupgradeBoMain,
  modAutoupgradeBoModal,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';
import {exec, execSync} from 'child_process';

const psVersion = utilsTest.getPSVersion();

/*
  Upgrade using the online channel
 */
test.describe('Upgrade using the online channel', () => {
  let browserContext: BrowserContext;
  let page: Page;
  let isModalVisible: boolean = true;

  test.beforeAll(async ({browser}) => {
    if (semver.lt(psVersion, '8.0.0')) {
      execSync('docker exec -t prestashop chmod +x /usr/local/bin/post-install.sh');
      execSync('docker compose exec prestashop /usr/local/bin/post-install.sh', {stdio: 'inherit'});
    }
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

  test('should get if the update modal is visible', async () => {
    isModalVisible = await modAutoupgradeBoModal.isModalVisible(page);
  });

  test('should check the update link', async () => {
    if (isModalVisible) {
      const updateLink = await modAutoupgradeBoModal.getUpdateLinkFromModal(page);
      expect(updateLink).toContain('https://build.prestashop-project.org/news');
    } else {
      test.skip();
    }
  });

  test('should click on the update link from the modal', async () => {
    if (isModalVisible) {
      page = await modAutoupgradeBoModal.openUpdateLinkFromTheModal(page);

      const pageTitle = await modAutoupgradeBoModal.getPageTitle(page);
      expect(pageTitle).toContain('PrestaShop');
      expect(pageTitle.toLowerCase()).toContain('available');

      page = await modAutoupgradeBoModal.closePage(browserContext, page, 0);
    } else {
      test.skip();
    }
  });

  test('should check the support link', async () => {
    if (isModalVisible) {
      const supportLink = await modAutoupgradeBoModal.getSupportLinkFromModal(page);
      expect(supportLink).toEqual('https://www.prestashop-project.org/support/');
    } else {
      test.skip();
    }
  });

  test('should click on Update and check the PS version', async () => {
    if (isModalVisible) {
      const version = await modAutoupgradeBoModal.getPSVersionFromTheModal(page);

      await modAutoupgradeBoModal.clickOnUpdateButton(page);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);

      const currentVersion = await modAutoupgradeBoMain.getCurrentPSAndPHPVersion(page);
      expect(currentVersion).not.toContain(version);
    } else {
      test.skip();
    }
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

  test('should check if the recommanded version is displayed', async () => {
    if (isModalVisible) {
      const isVisible = await modAutoupgradeBoMain.isRecommandedVersionVisible(page);
      expect(isVisible).toEqual(true);
    } else {
      test.skip();
    }
  });

  test('should choose the version to update and check requirements block', async () => {
    test.setTimeout(100_000);
    // Choose the major version if the modal is not visible
    // Choose the recommanded version if the modal is visible
    const isVisible = await modAutoupgradeBoMain.isRecommandedVersionVisible(page);
    const isRequirementBlockVisible = await modAutoupgradeBoMain.chooseNewVersion(page, isVisible);
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

    const isNextButtonEnabled = await modAutoupgradeBoMain.checkRequirements(page);
    expect(isNextButtonEnabled).toEqual(true);
  });

  test('should check the current PS version', async () => {
    const currentVersion = await modAutoupgradeBoMain.getCurrentPSAndPHPVersion(page);
    expect(currentVersion).toContain(psVersion);
  });

  test('should check the new PS version', async () => {
    const newVersion = await modAutoupgradeBoMain.getNewPSVersion(page);
    expect(newVersion).not.toContain(process.env.PS_VERSION);
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
  });

  test('should check the title of the last step', async () => {
    const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
    expect(stepTitle).toEqual('Post-update checklist');

    await page.waitForTimeout(20000);
  });
});
