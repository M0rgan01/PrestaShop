import BOBasePage from '@pages/BO/BObasePage';

import type {Page} from 'playwright';

/**
 * Theme & Logo base page, contains functions that can be used on the page
 * @class
 * @type {themeAndLogoBasePage}
 * @extends BOBasePage
 */
export default class themeAndLogoBasePage extends BOBasePage {
  private readonly advancedCustomizationNavItemLink: string;

  private readonly pagesConfigurationNavItemLink: string;

  private readonly themeShopCard: string;

  private readonly cardTheme: (themeName: string) => string;

  private readonly useThemeButton: (themeName: string) => string;

  private readonly useThemeModalDialog: string;

  protected readonly useThemeModalDialogYesButton: string;

  private readonly deleteThemeButton: (themeName: string) => string;

  private readonly deleteThemeModalDialog: string;

  private readonly deleteThemeModalDialogYesButton: string;

  protected growlDiv: string;

  protected growlMessageBlock: string;

  protected growlCloseButton: string;

  /**
   * @constructs
   * Setting up texts and selectors to use on theme & logo page
   */
  constructor() {
    super();
    this.pagesConfigurationNavItemLink = '#subtab-AdminPsThemeCustoConfiguration';
    this.advancedCustomizationNavItemLink = '#subtab-AdminPsThemeCustoAdvanced';
    this.themeShopCard = '.card-header[data-role="theme-shop"]';
    this.cardTheme = (themeName: string) => `.card-body div[data-role=${themeName}]`;
    this.useThemeButton = (themeName: string) => `form[action*=${themeName}] .action-button.js-display-use-theme-modal`;
    this.useThemeModalDialog = '#use_theme_modal .modal-dialog';
    this.useThemeModalDialogYesButton = `${this.useThemeModalDialog} .js-submit-use-theme`;
    this.deleteThemeButton = (themeName: string) => `form[action*=${themeName}] .delete-button`;
    this.deleteThemeModalDialog = '#delete_theme_modal .modal-dialog';
    this.deleteThemeModalDialogYesButton = `${this.deleteThemeModalDialog} .js-submit-delete-theme`;

    // Growls
    this.growlDiv = '#growls';
    this.growlMessageBlock = `${this.growlDiv} .growl-message`;
    this.growlCloseButton = `${this.growlDiv} .growl-close`;
  }

  /* Methods */
  /**
   * Get growl message content
   * @param page {Page} Browser tab
   * @param timeout {number} Timeout to wait for the selector
   * @return {Promise<string|null>}
   */
  async getGrowlMessageContent(page: Page, timeout: number = 10000): Promise<string | null> {
    return page.locator(this.growlMessageBlock).first().textContent({timeout});
  }

  /**
   * Close growl message
   * @param page {Page} Browser tab
   * @return {Promise<void>}
   */
  async closeGrowlMessage(page: Page): Promise<void> {
    let growlNotVisible = await this.elementNotVisible(page, this.growlMessageBlock, 10000);

    while (!growlNotVisible) {
      try {
        await page.locator(this.growlCloseButton).click();
      } catch (e) {
        // If element does not exist it's already not visible
      }

      growlNotVisible = await this.elementNotVisible(page, this.growlMessageBlock, 2000);
    }

    await this.waitForHiddenSelector(page, this.growlMessageBlock);
  }

  /**
   * Is present pages configuration tab
   * @param page {Page} Browser tab
   * @return {Promise<boolean>}
   */
  async hasSubTabPagesConfiguration(page: Page): Promise<boolean> {
    return this.elementVisible(page, this.pagesConfigurationNavItemLink);
  }

  /**
   * Go to pages configuration page
   * @param page {Page} Browser tab
   * @return {Promise<void>}
   */
  async goToSubTabPagesConfiguration(page: Page): Promise<void> {
    await this.clickAndWaitForURL(page, this.pagesConfigurationNavItemLink);
  }

  /**
   * Is present advanced customization tab
   * @param page {Page} Browser tab
   * @return {Promise<boolean>}
   */
  async hasSubTabAdvancedCustomization(page: Page): Promise<boolean> {
    return this.elementVisible(page, this.advancedCustomizationNavItemLink);
  }

  /**
   * Go to advanced customization page
   * @param page {Page} Browser tab
   * @return {Promise<void>}
   */
  async goToSubTabAdvancedCustomization(page: Page): Promise<void> {
    await this.clickAndWaitForURL(page, this.advancedCustomizationNavItemLink);
  }

  /**
   * Use the Theme
   * @param page {Page} Browser tab
   * @param themeName {string} The theme name
   * @returns {Promise<String>}
   */
  async useTheme(page: Page, themeName: string): Promise<string> {
    await this.scrollTo(page, this.themeShopCard);
    await page.hover(this.cardTheme(themeName));
    await this.waitForSelectorAndClick(page, this.useThemeButton(themeName));
    await this.waitForSelectorAndClick(page, this.useThemeModalDialogYesButton);

    return this.getAlertSuccessBlockParagraphContent(page);
  }

  /**
   * Delete the not used Theme
   * @param page {Page} Browser tab
   * @param themeName {string} The theme name
   * @returns {Promise<String>}
   */
  async deleteTheme(page: Page, themeName: string): Promise<string> {
    await this.scrollTo(page, this.themeShopCard);
    await page.hover(this.cardTheme(themeName));
    await this.waitForSelectorAndClick(page, this.deleteThemeButton(themeName));
    await this.waitForSelectorAndClick(page, this.deleteThemeModalDialogYesButton);

    return this.getAlertSuccessBlockParagraphContent(page);
  }
}
