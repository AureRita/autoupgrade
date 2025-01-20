import DialogAbstract from './DialogAbstract';

export default class StartBackupDialog extends DialogAbstract {
  protected readonly formId = 'form-confirm-backup';

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }
}
