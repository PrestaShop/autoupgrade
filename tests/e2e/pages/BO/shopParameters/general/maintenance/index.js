// Get resolver
const VersionSelectResolver = require('prestashop_test_lib/kernel/resolvers/versionSelectResolver');

const configClassMap = require('@root/configClassMap.js');

const versionSelectResolver = new VersionSelectResolver(global.PS_RESOLVER_VERSION.FROM, configClassMap);

// Import BOBasePage
const BoBasePage = versionSelectResolver.require('BO/BObasePage.js');

class Maintenance extends BoBasePage {
  constructor() {
    super();

    this.pageTitle = 'Maintenance';
    this.storeStatusRadio = status => `#form_general_enable_shop_${status}, #form_enable_shop_${status}`;
    this.addIpButton = '.add_ip_button';
    this.saveButton = '.card-footer button';
  }

  // Methods

  /**
   * Set shop status
   * @param page {Page} Browser tab
   * @param status {boolean} Status for the shop
   * @returns {Promise<void>}
   */
  async setShopStatus(page, status = true) {
    // eslint-disable-next-line no-return-assign,no-param-reassign
    await page.$eval(this.storeStatusRadio(status ? '1' : '0'), el => el.checked = true);
    await this.clickAndWaitForNavigation(page, this.saveButton);
  }

  /**
   * Add my ip to shop
   * @param page {Page} Browser tab
   * @returns {Promise<void>}
   */
  async addMyIp(page) {
    await page.click(this.addIpButton);
    await this.clickAndWaitForNavigation(page, this.saveButton);
  }
}

module.exports = new Maintenance();
