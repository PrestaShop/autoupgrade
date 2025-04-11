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
// Import utils
import {
  boDashboardPage,
  boEmailPage,
  boLoginPage,
  dataCustomers,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page,
} from '@playwright/test';

const {smtpServer, smtpPort} = {
  smtpPort: 1025,
  smtpServer: 'maildev',
};

/**
 * Setup SMTP configuration
 */
const setupSmtpConfigTest = async (page: Page): Promise<void> => {
  await test.step('should login in BO', async () => {
    await boLoginPage.goTo(page, global.BO.URL);
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).toContain(boDashboardPage.pageTitle);
  });

  await test.step('should go to \'Advanced Parameters > E-mail\' page', async () => {
    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.emailLink,
    );
    await boEmailPage.closeSfToolBar(page);

    const pageTitle = await boEmailPage.getPageTitle(page);
    expect(pageTitle).toContain(boEmailPage.pageTitle);
  });

  await test.step('should fill the smtp parameters form fields', async () => {
    const alertSuccessMessage = await boEmailPage.setupSmtpParameters(
      page,
      smtpServer,
      dataCustomers.johnDoe.email,
      dataCustomers.johnDoe.password,
      smtpPort.toString(),
    );
    expect(alertSuccessMessage).toContain(boEmailPage.successfulUpdateMessage);
  });
};

export default setupSmtpConfigTest;
