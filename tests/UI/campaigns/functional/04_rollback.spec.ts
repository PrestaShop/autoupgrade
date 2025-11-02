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
  utilsFile,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boInstalledModulesPage,
  boModuleManagerPage,
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
  Rollback
 */
test.describe('Rollback', () => {
  let browserContext: BrowserContext;
  let page: Page;
  let filePath: string | null;

  test.beforeAll(async ({browser}) => {
    browserContext = await browser.newContext();
    page = await browserContext.newPage();
  });
  test.afterAll(async () => {
    await page.close();
  });

  // Steps
  test.describe('Go to module configuration page', () => {
    test('should login in BO', async () => {
      await boLoginPage.goTo(page, global.BO.URL);
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);
      execSync('bash ./scripts/post_install.sh', {stdio: 'inherit'});

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test('should close update notification dialog', async () => {
      const isDialogNotVisible = await modAutoupgradeBoModal.closeDialogUpdateNotification(page);
      expect(isDialogNotVisible).toEqual(true);
    });

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
  });

  test.describe('Do a backup 3 times', () => {
    [1, 2, 3].forEach((index: number) => {
      test(`should click on update your store radio button then get started - ${index}`, async () => {
        const isStepContentVisible = await modAutoupgradeBoMain.updateYourStore(page);
        expect(isStepContentVisible).toEqual(true);

        const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
        expect(stepTitle).toEqual('Version choice');
      });

      test(`should choose the version to update and check requirements block - ${index}`, async () => {
        test.setTimeout(100_000);
        await exec('docker exec -t prestashop chmod -R 777 /var/www/html/modules');
        const isVisible = await modAutoupgradeBoMain.isRecommandedVersionVisible(page);
        await modAutoupgradeBoMain.chooseNewVersion(page, isVisible);
      });

      if (index === 1) {
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

        test(`should check that all the requirements are OK - ${index}`, async () => {
          await exec('docker exec -t prestashop chmod -R 777 /var/www/html/modules');

          const isNextButtonEnabled = await modAutoupgradeBoMain.checkRequirements(page);
          expect(isNextButtonEnabled).toEqual(true);
        });
      }

      test(`should click on next button and check that the step title is 'Update options' - ${index}`, async () => {
        await modAutoupgradeBoMain.goToNextStep(page);

        const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
        expect(stepTitle).toEqual('Update options');
      });

      test(`should click on next button and check that the step title is 'Back up your store' - ${index}`, async () => {
        await modAutoupgradeBoMain.goToNextStep(page);

        const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
        expect(stepTitle).toEqual('Back up your store');
      });

      test(`should click on 'Launch backup' button and check the modal - ${index}`, async () => {
        const isModalVisible = await modAutoupgradeBoMain.clickOnLaunchBackup(page);
        expect(isModalVisible).toEqual(true);
      });

      test(`should click on 'Start backup' button - ${index}`, async () => {
        test.setTimeout(5000_000);

        const successMessage = await modAutoupgradeBoMain.startBackup(page);
        expect(successMessage)
          .toEqual('It is available at /your-admin-directory/autoupgrade/backup. You\'re ready to start the update now.');
      });

      test(`should go back to Update assistant page - ${index}`, async () => {
        await modAutoupgradeBoMain.clickOnUpdateAssistantLink(page);

        const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
        expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
      });
    });
  });

  test.describe('Restore from a backup', () => {
    test('should click on restore from a backup', async () => {
      const isStepContentVisible = await modAutoupgradeBoMain.restoreFromBackup(page);
      expect(isStepContentVisible).toEqual(true);

      const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
      expect(stepTitle).toEqual('Backup selection');
    });

    test('should check that the number of backups is 3', async () => {
      const numberOfBackups = await modAutoupgradeBoMain.getNumberOfBackups(page);
      expect(numberOfBackups).toEqual(3);
    });

    test('should click on the button \'Delete selection\' then cancel', async () => {
      const isModalVisible = await modAutoupgradeBoMain.backupClickDeleteSelection(page);
      expect(isModalVisible).toEqual(true);

      const isModalNotVisible = await modAutoupgradeBoMain.cancelDeleteBackup(page);
      expect(isModalNotVisible).toEqual(true);
    });

    test('should click on the button \'Delete selection\', confirm then check the number of backups', async () => {
      const isModalVisible = await modAutoupgradeBoMain.backupClickDeleteSelection(page);
      expect(isModalVisible).toEqual(true);

      const isModalNotVisible = await modAutoupgradeBoMain.deleteBackup(page);
      expect(isModalNotVisible).toEqual(true);

      const numberOfBackups = await modAutoupgradeBoMain.getNumberOfBackups(page);
      expect(numberOfBackups).toEqual(2);
    });

    test('should delete the backup from the project, click on restore and check the error message', async () => {
      const backupName = await modAutoupgradeBoMain.getSelectedBackupName(page);

      await exec(`docker exec -t prestashop rm -R /var/www/html/admin-dev/autoupgrade/backup/${backupName}`);
      await page.waitForTimeout(5000);

      const isStepContentVisible = await modAutoupgradeBoMain.clickOnRestoreButton(page);
      expect(isStepContentVisible).toEqual(false);

      const errorMessage = await modAutoupgradeBoMain.getRestoreErrorMessage(page);
      expect(errorMessage).toContain(`Invalid configuration, backup ${backupName} doesn't exist.`);
    });

    test('should check the number of backup', async () => {
      const numberOfBackups = await modAutoupgradeBoMain.getNumberOfBackups(page);
      expect(numberOfBackups).toEqual(1);
    });

    test('should go back to Update assistant page', async () => {
      await modAutoupgradeBoMain.clickOnUpdateAssistantLink(page);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
    });

    test('should click on restore from a backup - 2', async () => {
      const isStepContentVisible = await modAutoupgradeBoMain.restoreFromBackup(page);
      expect(isStepContentVisible).toEqual(true);

      const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
      expect(stepTitle).toEqual('Backup selection');
    });

    test('should click on restore and wait until the end of restore', async () => {
      test.setTimeout(5000_000);

      const isModalVisible = await modAutoupgradeBoMain.clickOnRestoreButton(page);
      expect(isModalVisible).toEqual(true);

      const successMessage = await modAutoupgradeBoMain.confirmRestore(page);
      expect(successMessage).toEqual('Your restoration is complete');
    });

    test('should download restore logs', async () => {
      filePath = await modAutoupgradeBoMain.downloadRestoreLogs(page);

      const exist = await utilsFile.doesFileExist(filePath);
      expect(exist, 'File does not exist').toEqual(true);
    });

    test('should open developer documentation then close the page', async () => {
      page = await modAutoupgradeBoMain.openDeveloperDocumentation(page);

      page = await boMaintenancePage.closePage(browserContext, page, 0);
    });

    test('should click on exit button', async () => {
      await modAutoupgradeBoMain.clickOnExitPostRestore(page);

      const isDialogNotVisible = await modAutoupgradeBoModal.closeDialogUpdateNotification(page);
      expect(isDialogNotVisible).toEqual(true);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test('should click on Update assistant from the sideboard', async () => {
      await modAutoupgradeBoMain.goToUpdateAssistantPage(page);

      const pageTitle = await modAutoupgradeBoMain.getPageTitle(page);
      expect(pageTitle).toEqual(modAutoupgradeBoMain.pageTitle);
    });

    test('should click on restore from a backup - 3', async () => {
      const isStepContentVisible = await modAutoupgradeBoMain.restoreFromBackup(page);
      expect(isStepContentVisible).toEqual(true);

      const stepTitle = await modAutoupgradeBoMain.getStepTitle(page);
      expect(stepTitle).toEqual('Backup selection');
    });

    test('should check the number of backups from the list', async () => {
      const numberOfBackups = await modAutoupgradeBoMain.getNumberOfBackups(page);
      expect(numberOfBackups).toEqual(1);
    });

    test('should click on the button \'Delete selection\' and confirm', async () => {
      const isModalVisible = await modAutoupgradeBoMain.backupClickDeleteSelection(page);
      expect(isModalVisible).toEqual(true);

      await modAutoupgradeBoMain.deleteBackup(page);
    });

    test('should check the message \'No backup file found on your store\'', async () => {
      const message = await modAutoupgradeBoMain.getNoBackupInYourStoreMessage(page);
      expect(message).toEqual('No backup file found on your store.');
    });
  });
});
