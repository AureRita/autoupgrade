import DialogAbstract from './DialogAbstract';

export default class DeleteBackupDialog extends DialogAbstract {
  protected readonly formId = 'backup_to_delete';

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToConfirmDelete'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }
}
