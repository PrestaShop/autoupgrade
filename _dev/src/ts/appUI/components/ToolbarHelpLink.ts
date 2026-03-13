/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

class ToolbarHelpLink {
  public get helpLink(): HTMLElement {
    const container = document.querySelector('.toolbar_btn.btn-help');

    if (!container || !(container instanceof HTMLElement)) {
      throw new Error('Cannot find help link to initialize.');
    }
    return container;
  }

  public updateHelpLink = (): void => {
    this.helpLink.setAttribute('href', window.AutoUpgradeVariables.links.help);
    this.helpLink.setAttribute('target', '_blank');
    this.helpLink.addEventListener('click', this.#onClick, true);
  };

  #onClick(ev: Event): void {
    ev.stopImmediatePropagation();
  }
}

export default ToolbarHelpLink;
