import {
    // Import utils
    testContext,
    // Import BO pages
    boDashboardPage,
    boLoginPage,
    boOrdersPage,
    boOrdersViewProductsBlockPage,
    // Import data
    dataOrders,
    dataOrderStatuses,
} from '@prestashop-core/ui-testing';

import {test, expect, Page, BrowserContext} from '@playwright/test';

const baseContext: string = 'sanity_ordersBO_editOrder';

/*
  Connect to the BO
  Edit the first order
  Logout from the BO
 */
test.describe('BO - Orders - Orders : Edit Order BO', async () => {
    let browserContext: BrowserContext;
    let page: Page;

    // before and after functions
    test.beforeAll(async ({browser}) => {
        browserContext = await browser.newContext();
        page = await browserContext.newPage();
    });
    test.afterAll(async () => {
        await page.close();
    });

    // Steps
    test('should login in BO', async function () {
        await testContext.addContextItem(test.info(), 'testIdentifier', 'loginBO', baseContext);

        await boLoginPage.goTo(page, global.BO.URL);
        await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

        const pageTitle = await boDashboardPage.getPageTitle(page);
        expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test('should go to the \'Orders > Orders\' page', async function () {
        await testContext.addContextItem(test.info(), 'testIdentifier', 'goToOrdersPage', baseContext);

        await boDashboardPage.goToSubMenu(
            page,
            boDashboardPage.ordersParentLink,
            boDashboardPage.ordersLink,
        );
        await boOrdersPage.closeSfToolBar(page);

        const pageTitle = await boOrdersPage.getPageTitle(page);
        expect(pageTitle).toContain(boOrdersPage.pageTitle);
    });

  /*  test('should go to the first order page', async function () {
        await testContext.addContextItem(test.info(), 'testIdentifier', 'goToFirstOrder', baseContext);

        await boOrdersPage.goToOrder(page, 1);

        const pageTitle = await boOrdersViewProductsBlockPage.getPageTitle(page);
        expect(pageTitle).toContain(boOrdersViewProductsBlockPage.pageTitle);
    });

    test('should modify the product quantity and check the validation', async function () {
        await testContext.addContextItem(test.info(), 'testIdentifier', 'editOrderQuantity', baseContext);

        const newQuantity = await boOrdersViewProductsBlockPage.modifyProductQuantity(page, 1, 5);
        expect(newQuantity, 'Quantity was not updated').toEqual(5);
    });

    test('should modify the order status and check the validation', async function () {
        await testContext.addContextItem(test.info(), 'testIdentifier', 'editOrderStatus', baseContext);

        const orderStatus = await boOrdersViewProductsBlockPage.modifyOrderStatus(page, dataOrderStatuses.paymentAccepted.name);
        expect(orderStatus).toEqual(dataOrderStatuses.paymentAccepted.name);
    });*/

    // Logout from BO
    test('should log out from BO', async function () {
        await testContext.addContextItem(test.info(), 'testIdentifier', 'logoutBO', baseContext);

        await boLoginPage.logoutBO(page);

        const pageTitle = await boLoginPage.getPageTitle(page);
        expect(pageTitle).toContain(boLoginPage.pageTitle);
    });
});