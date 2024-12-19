import {
  // Import utils
  utilsTest,
  // Import BO pages
  boDashboardPage,
  boLoginPage,
  boModuleManagerPage,
  boInstalledModulesPage,
  boModuleSelectionPage,
  boModuleCatalogPage,
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

  test('should display the new interface', async ()=> {
    const url = await modAutoupgradeBoMain.getCurrentURL(page);
    console.log(url);
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
