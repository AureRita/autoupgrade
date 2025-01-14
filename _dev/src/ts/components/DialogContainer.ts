import DomLifecycle from '../types/DomLifecycle';
import Hydration from '../utils/Hydration';
import { scriptHandler } from '../autoUpgrade';
import { ScriptType } from '../types/scriptHandlerTypes';

export default class DialogContainer implements DomLifecycle {
  public static readonly cancelEvent = 'cancel';
  public static readonly okEvent = 'ok';

  public static readonly containerId = 'ua_dialog';

  public mount(): void {
    this.DialogContainer.addEventListener(Hydration.hydrationEventName, this.#displayDialog);
    this.DialogContainer.addEventListener('click', this.#onClick);
    this.DialogContainer.addEventListener(DialogContainer.cancelEvent, this.#closeDialog);
    this.DialogContainer.addEventListener(DialogContainer.okEvent, this.#closeDialog);
  }

  public beforeDestroy(): void {
    this.DialogContainer.removeEventListener(Hydration.hydrationEventName, this.#displayDialog);
    this.DialogContainer.removeEventListener('click', this.#onClick);
    this.DialogContainer.removeEventListener(DialogContainer.cancelEvent, this.#closeDialog);
    this.DialogContainer.removeEventListener(DialogContainer.okEvent, this.#closeDialog);
  }

  public get DialogContainer(): HTMLElement {
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
      } else if (target?.closest(".dialog__footer button:not([data-dismiss='dialog'])")) {
        dialog.dispatchEvent(new Event(DialogContainer.okEvent, { bubbles: true }));
      }
    }
  }

  #closeDialog(ev: Event): void {
    scriptHandler.unloadScript(ScriptType.DIALOG);
    const dialog = ev.target as HTMLDialogElement;
    if (dialog) {
      dialog.close();
    }
  }
}
