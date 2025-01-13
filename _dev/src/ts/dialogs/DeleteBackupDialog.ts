import DomLifecycle from '../types/DomLifecycle';
import api from '../api/RequestHandler';

export default class DeleteBackupDialog implements DomLifecycle {
  protected readonly formId = 'backup_to_delete';

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
    if (!form.dataset.routeToConfirmDelete) {
      throw new Error(`Missing data route to confirm delete from form dataset.`);
    }

    return form;
  }

  #onSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToConfirmDelete!, new FormData(this.#form));
  };
}
