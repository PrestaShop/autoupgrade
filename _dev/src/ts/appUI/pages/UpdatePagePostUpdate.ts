/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';
import api from '../api/RequestHandler';

export default class UpdatePagePostUpdate extends StepPage {
  protected stepCode = 'post-update';

  public mount() {
    this.initStepper();
    this.#dialogConfirmModuleManagerLink.addEventListener('click', this.#onClickDialogLink);
  }

  public beforeDestroy() {
    this.#dialogConfirmModuleManagerLink.removeEventListener('click', this.#onClickDialogLink);
  }

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

  get #dialogConfirmModuleManagerLink(): HTMLAnchorElement {
    const link = document.getElementById('dialog-confirm-module-manager-link');
    if (!link) {
      throw new Error('Dialog trigger link ID was not found');
    }
    return link as HTMLAnchorElement;
  }
}
