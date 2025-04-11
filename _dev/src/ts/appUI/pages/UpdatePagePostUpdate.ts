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
import StepPage from './StepPage';
import api from '../api/RequestHandler';

export default class UpdatePagePostUpdate extends StepPage {
  protected stepCode = 'post-update';

  public mount() {
    this.initStepper();
    this.#handleHydrate();
  }

  #handleHydrate = () => {
    this.#addListenerToDialogConfirmModuleManagerLink();
  };

  #onClickDialogLink = async (event: MouseEvent) => {
    const target = event.target as HTMLAnchorElement;

    // Checks if the clicked element is an <a> tag pointing towards an ID
    if (!target || target.tagName !== 'A' || !target.hash) {
      return;
    }

    event.preventDefault();

    const hashRoute = target.hash.substring(1);
    await api.post(hashRoute);
  };

  #addListenerToDialogConfirmModuleManagerLink = () => {
    this.#dialogConfirmModuleManagerLink.addEventListener('click', this.#onClickDialogLink);
  };

  get #dialogConfirmModuleManagerLink(): HTMLAnchorElement {
    const link = document.getElementById('dialog-confirm-module-manager-link');
    if (!link) {
      throw new Error('Dialog trigger link ID was not found');
    }
    return link as HTMLAnchorElement;
  }  
}
