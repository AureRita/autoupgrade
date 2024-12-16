import {
  // Import utils
  utilsTest,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boModuleManagerPage,
  boModuleManagerUninstalledModulesPage,
  boMaintenancePage,
  dataModules,
  modAutoupgradeBoMain,
} from '@prestashop-core/ui-testing';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';
import semver from 'semver';

const psVersion = utilsTest.getPSVersion();

/*
  Verify the New UI
 */
test.describe('Verify the New UI', () => {
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

  if (semver.lt(psVersion, '8.0.0')) {
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

  /*test('should go to maintenance page', async () => {
    page = await modAutoupgradeBoMain.goToMaintenancePage(page);

    const pageTitle = await boMaintenancePage.getPageTitle(page);
    expect(pageTitle).toContain(boMaintenancePage.pageTitle);
  });

  test('should disable the store', async () => {
    const result = await boMaintenancePage.changeShopStatus(page, false);
    expect(result).toContain(boMaintenancePage.successfulUpdateMessage);
  })

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
    await modAutoupgradeBoMain.reloadPage(page);

    const isAlertDangerVisible = await modAutoupgradeBoMain.isRequirementsAlertDangerVisible(page);
    expect(isAlertDangerVisible).toEqual(false);
  });*/
});
