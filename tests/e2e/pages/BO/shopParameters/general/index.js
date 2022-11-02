// Get resolver
const VersionSelectResolver = require('prestashop_test_lib/kernel/resolvers/versionSelectResolver');

const configClassMap = require('@root/configClassMap.js');

const versionSelectResolver = new VersionSelectResolver(global.PS_RESOLVER_VERSION.FROM, configClassMap);

// Import BOBasePage
const BoBasePage = versionSelectResolver.require('BO/BObasePage.js');

class General extends BoBasePage {
  constructor() {
    super();

    this.pageTitle = 'Preferences';

    this.maintenanceSubTabLink = '#subtab-AdminMaintenance';
  }

  // Methods
  /**
   * Go to maintenance tab
   * @param page {Page} Browser tab
   * @returns {Promise<void>}
   */
  async goToMaintenanceTab(page) {
    await this.clickAndWaitForNavigation(page, this.maintenanceSubTabLink);
  }
}

module.exports = new General();
