// Get resolver
const VersionSelectResolver = require('prestashop_test_lib/kernel/resolvers/versionSelectResolver');

const configClassMap = require('@root/configClassMap.js');

const versionSelectResolver = new VersionSelectResolver(global.PS_RESOLVER_VERSION.FROM, configClassMap);

// Import BOBasePage
const ModuleConfigurationPage = versionSelectResolver.require('BO/modules/moduleConfiguration/index.js');

class Upgrade extends ModuleConfigurationPage.constructor {
  constructor() {
    super();

    this.pageTitle = '1-Click Upgrade';
    this.configResultValidationMessage = 'Configuration successfully updated. ';
    this.upgradeValidationMessage = 'Upgrade complete';

    // Selectors
    // Current configuration form
    this.currentConfigurationForm = '#currentConfiguration';
    this.putShopUnderMaintenanceButton = `${this.currentConfigurationForm} input[name='putUnderMaintenance']`;
    this.checklistTableRow = row => `${this.currentConfigurationForm} tbody tr:nth-child(${row})`;
    this.checklistTableColumnImage = row => `${this.checklistTableRow(row)} td img`;

    // Start your upgrade form
    this.upgradeNowButton = '#upgradeNow';
    this.currentlyProcessingDiv = '#currentlyProcessing';
    this.alertSuccess = '#upgradeResultCheck.alert-success';

    // Expert mode form
    this.channelSelect = '#channel';
    this.archiveSelect = '#archive_prestashop';
    this.archiveNumber = '#archive_num';
    this.saveButton = '#advanced  input[name="submitConf-channel"]';
    this.configResultAlert = '#configResult';
  }

  // Methods

  /**
   * Fill expert mode form
   * @param page
   * @param channel
   * @param archive
   * @param newVersion
   * @returns {Promise<string>}
   */
  async fillExpertModeForm(page, channel, archive, newVersion) {
    await this.reloadPage(page);
    await this.selectByVisibleText(page, this.channelSelect, channel);
    await this.selectByVisibleText(page, this.archiveSelect, archive);
    await this.setValue(page, this.archiveNumber, newVersion);

    const [configResultMessage] = await Promise.all([
      this.getTextContent(page, this.configResultAlert),
      this.clickAndWaitForNavigation(page, this.saveButton),
    ]);

    return configResultMessage;
  }

  /**
   * Put shop under maintenance
   * @param page
   * @returns {Promise<void>}
   */
  async putShopUnderMaintenance(page) {
    if (!(await this.elementNotVisible(page, this.putShopUnderMaintenanceButton, 2000))) {
      await this.clickAndWaitForNavigation(page, this.putShopUnderMaintenanceButton);
    }
  }

  /**
   * Get all checklist image column content
   * @param page
   * @param row
   * @returns {Promise<string>}
   */
  async getRowImageContent(page, row) {
    return this.getAttributeContent(page, this.checklistTableColumnImage(row), 'alt');
  }

  /**
   * Wait for upgrade
   * @param page
   * @param timeDelay
   * @returns {Promise<string>}
   */
  async waitForUpgrade(page, timeDelay) {
    let upgradeFinished = false;
    let i = 0;

    while (!upgradeFinished && i < timeDelay) {
      upgradeFinished = await this.elementVisible(page, this.alertSuccess, 200);
      i += 200;
    }

    if (upgradeFinished) {
      return this.getTextContent(page, this.alertSuccess);
    }

    throw new Error(`Upgrade is not complete after ${timeDelay / 1000}sec`);
  }

  /**
   * Upgrade prestashop now
   * @param page
   * @returns {Promise<string>}
   */
  async upgradePrestaShopNow(page) {
    await page.click(this.upgradeNowButton);
    await this.waitForVisibleSelector(page, this.currentlyProcessingDiv);

    return this.waitForUpgrade(page, 500000);
  }
}

module.exports = new Upgrade();
