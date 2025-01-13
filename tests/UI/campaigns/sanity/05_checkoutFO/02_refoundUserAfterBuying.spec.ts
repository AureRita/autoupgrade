import {
    //import BO
    boDashboardPage,
    boLoginPage,
    boOrdersPage,
    //import data
    dataCustomers,
    dataPaymentMethods,
    dataOrderStatuses,
    // import FO page 
    foClassicCartPage,
    foClassicCheckoutOrderConfirmationPage,
    foClassicCheckoutPage,
    foClassicHomePage,
    foClassicLoginPage,
    foClassicModalBlockCartPage,
    foClassicModalQuickViewPage,
    FOBasePage,
    foClassicMyAccountPage,
    foClassicOrderHistoryPage,
} from '@prestashop-core/ui-testing';

import {
    test, expect, Page, BrowserContext,
} from '@playwright/test';
import orderStatuses from '@prestashop-core/ui-testing/dist/data/demo/orderStatuses';
import { P } from 'reports/trace/assets/inspectorTab-CU3eUCmV';

const baseContext: string = 'sanity_checkoutFO_refoundUserAfterBuying';

test.describe('FO - Checkout : Refound an user after buying something' , async() => {
    let browserContext: BrowserContext;
    let page : Page;
    
    test.beforeAll(async ({browser}) => {
        browserContext = await browser.newContext();
        page = await browserContext.newPage();
    });
        test.afterAll(async () => {
        await page.close();
    });
      
// Steps 
    test('should open the shop page', async() => {
        await foClassicHomePage.goTo(page, global.FO.URL);

        const result = await foClassicHomePage.isHomePage(page);
        expect(result).toEqual(true);
    });

    test('should go to login page', async () => {
        await foClassicHomePage.goToLoginPage(page);
    
        const pageTitle = await foClassicLoginPage.getPageTitle(page);
        expect(pageTitle).toEqual(foClassicLoginPage.pageTitle);
    });
    
    test('should sign In in FO with default account', async () => {    
        await foClassicLoginPage.customerLogin(page, dataCustomers.johnDoe);
    
        const connected = await foClassicHomePage.isCustomerConnected(page);
        expect(connected, 'Customer is not connected in FO').toEqual(true);
    });
    
    test('should go to home page', async () => {  
        const isHomepage = await foClassicHomePage.isHomePage(page);
    
        if (!isHomepage) {
          await foClassicHomePage.goToHomePage(page);
        }
    
        const result = await foClassicHomePage.isHomePage(page);
        expect(result).toEqual(true);
    });

    test('should quick view the first product', async () => {    
        await foClassicHomePage.goToHomePage(page);
        await foClassicHomePage.quickViewProduct(page, 1);
    
        const isQuickViewModalVisible = await foClassicModalQuickViewPage.isQuickViewProductModalVisible(page);
        expect(isQuickViewModalVisible).toEqual(true);
    });
    test('should add first product to cart and Proceed to checkout', async () => {    
        await foClassicModalQuickViewPage.addToCartByQuickView(page);
        await foClassicModalBlockCartPage.proceedToCheckout(page);
    
        const pageTitle = await foClassicCartPage.getPageTitle(page);
        expect(pageTitle).toEqual(foClassicCartPage.pageTitle);
    });

    test('should proceed to checkout and check Step Address', async () => {    
        await foClassicCartPage.clickOnProceedToCheckout(page);
    
        const isCheckoutPage = await foClassicCheckoutPage.isCheckoutPage(page);
        expect(isCheckoutPage, 'Browser is not in checkout Page').toEqual(true);
    
        const isStepPersonalInformationComplete = await foClassicCheckoutPage.isStepCompleted(
          page,
          foClassicCheckoutPage.personalInformationStepForm,
        );
        expect(isStepPersonalInformationComplete, 'Step Personal information is not complete').toEqual(true);
    });
    
    test('should validate Step Address and go to Delivery Step', async () => {    
        const isStepAddressComplete = await foClassicCheckoutPage.goToDeliveryStep(page);
        expect(isStepAddressComplete, 'Step Address is not complete').toEqual(true);
    });
    
    test('should validate Step Delivery and go to Payment Step', async () => {    
        const isStepDeliveryComplete = await foClassicCheckoutPage.goToPaymentStep(page);
        expect(isStepDeliveryComplete, 'Step Address is not complete').toEqual(true);
    });
    
    test('should Pay by bank wire and confirm order', async () => {    
        await foClassicCheckoutPage.choosePaymentAndOrder(page, dataPaymentMethods.wirePayment.moduleName);
    
        const pageTitle = await foClassicCheckoutOrderConfirmationPage.getPageTitle(page);
        expect(pageTitle).toEqual(foClassicCheckoutOrderConfirmationPage.pageTitle);
    
        const cardTitle = await foClassicCheckoutOrderConfirmationPage.getOrderConfirmationCardTitle(page);
        expect(cardTitle).toContain(foClassicCheckoutOrderConfirmationPage.orderConfirmationCardTitle);
    });

    test('should login in BO', async () => {
        page = await browserContext.newPage();
        await boLoginPage.goTo(page, global.BO.URL);
        await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);
    
        const pageTitle = await boDashboardPage.getPageTitle(page);
        expect(pageTitle).toContain(boDashboardPage.pageTitle);
    });

    test('should go to the \'Orders > Orders\' page', async () => {  
        await boDashboardPage.goToSubMenu(
          page,
          boDashboardPage.ordersParentLink,
          boDashboardPage.ordersLink,
        );
        await boOrdersPage.closeSfToolBar(page);
    
        const pageTitle = await boOrdersPage.getPageTitle(page);
        expect(pageTitle).toContain(boOrdersPage.pageTitle);
    });

    test('should modify the dropdown order state', async () => {
        const modalUpdate = await boOrdersPage.setOrderStatus(page,1,dataOrderStatuses.refunded);
        expect(modalUpdate).toEqual(boOrdersPage.successfulUpdateMessage);
    });
    

    test('Should return to the customer account page',async() => {
        page = await boOrdersPage.changePage(browserContext,0);
        await foClassicCheckoutOrderConfirmationPage.goToMyAccountPage(page);
        const pageTitle = await foClassicMyAccountPage.getPageTitle(page);
        expect(pageTitle).toEqual(foClassicMyAccountPage.pageTitle);
    });

    test('Go to your order account',async() => {
        await foClassicMyAccountPage.goToHistoryAndDetailsPage(page);
        const pageTitle= await foClassicOrderHistoryPage.getPageTitle(page);
        expect(pageTitle).toEqual(foClassicOrderHistoryPage.pageTitle);
    });

    test('Verify Status of the first Order',async () => {
        const status = await foClassicOrderHistoryPage.getOrderStatus(page,1);
        expect(status).toEqual(dataOrderStatuses.refunded.name);
    })
} )
