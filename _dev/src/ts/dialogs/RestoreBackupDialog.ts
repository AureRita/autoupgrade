import DomLifecycle from '../types/DomLifecycle';
import api from '../api/RequestHandler';

export default class RestoreBackupDialog implements DomLifecycle {
  protected readonly formId = 'backup_to_restore';

  public mount = (): void => {
    this.#form.addEventListener('submit', this.#onSubmit);
  };

  public beforeDestroy = (): void => {
    this.#form.removeEventListener('submit', this.#onSubmit);
  };

  get #form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }
    if (!form.dataset.routeToConfirmRestore) {
      throw new Error(`Missing data route to confirm restore from form dataset.`);
    }

    return form;
  }

  #onSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToConfirmRestore!, new FormData(this.#form));
  };
}
