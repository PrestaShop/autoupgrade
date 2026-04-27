/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DomLifecycle from '../../types/DomLifecycle';
import Hydration from '../utils/Hydration';
import { scriptHandler } from '../main';
import { ScriptType } from '../types/scriptHandlerTypes';

export default class DialogContainer implements DomLifecycle {
  public static readonly cancelEvent = 'cancel';
  public static readonly okEvent = 'ok';

  public static readonly containerId = 'ua_dialog';

  public mount(): void {
    this.dialogContainer.addEventListener(Hydration.hydrationEventName, this.#displayDialog);
    this.dialogContainer.addEventListener('click', this.#onClick);
    this.dialogContainer.addEventListener(DialogContainer.cancelEvent, this.#closeDialog);
    this.dialogContainer.addEventListener(DialogContainer.okEvent, this.#closeDialog);
  }

  public beforeDestroy(): void {
    this.dialogContainer.removeEventListener(Hydration.hydrationEventName, this.#displayDialog);
    this.dialogContainer.removeEventListener('click', this.#onClick);
    this.dialogContainer.removeEventListener(DialogContainer.cancelEvent, this.#closeDialog);
    this.dialogContainer.removeEventListener(DialogContainer.okEvent, this.#closeDialog);
  }

  public get dialogContainer(): HTMLElement {
    const container = document.getElementById(DialogContainer.containerId);

    if (!container) {
      throw new Error('Cannot find dialog container to initialize.');
    }
    return container;
  }

  #displayDialog(): void {
    const dialog = document
      .getElementById(DialogContainer.containerId)
      ?.getElementsByClassName('dialog')[0] as HTMLDialogElement;
    if (dialog) {
      dialog.showModal();
    }
  }

  #onClick(ev: Event): void {
    const target = ev.target ? (ev.target as HTMLElement) : null;
    const dialog = target?.closest('.dialog');

    if (dialog) {
      if (
        target?.closest("[data-dismiss='dialog']") ||
        !dialog.contains(target) ||
        target === dialog
      ) {
        dialog.dispatchEvent(new Event(DialogContainer.cancelEvent, { bubbles: true }));
      }
    }
  }

  #closeDialog(ev: Event): void {
    scriptHandler.unloadScriptType(ScriptType.DIALOG);
    const dialog = ev.target as HTMLDialogElement;
    if (dialog) {
      dialog.close();
    }
  }
}
