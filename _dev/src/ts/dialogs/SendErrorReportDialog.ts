import { sendUserFeedback } from '../api/sentryApi';
import { Feedback, FeedbackFields, Logs } from '../types/sentryApi';
import { logStore } from '../store/LogStore';
import { formatLogsMessages } from '../utils/logsUtils';
import DialogAbstract from './DialogAbstract';

export default class SendErrorReportDialog extends DialogAbstract {
  protected readonly formId = 'form-error-feedback';

  public mount = (): void => {
    this.form.addEventListener('submit', this.onSubmit);

    const errorMessageArea: HTMLTextAreaElement = this.form.querySelector('#errorMessage')!;
    errorMessageArea.value = this.#lastErrorMessage;
  };

  get form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    return form;
  }

  get #lastErrorMessage(): string {
    const latestError = logStore.getErrors().pop()?.message;

    if (!latestError) {
      throw new Error('No error message found to send');
    }

    return latestError;
  }

  onSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const logs = this.#getLogs();
    const feedback = this.#getFeedback(event.target as HTMLFormElement);

    sendUserFeedback(this.#lastErrorMessage, logs, feedback);

    this.dispatchDialogContainerOkEvent(event);
  };

  #getLogs(): Logs {
    return {
      logs: formatLogsMessages(logStore.getLogs()),
      warnings: formatLogsMessages(logStore.getWarnings()),
      errors: formatLogsMessages(logStore.getErrors())
    };
  }

  #getFeedback(form: HTMLFormElement): Feedback {
    const formData = new FormData(form);
    const feedback: Feedback = {};

    Object.values(FeedbackFields).forEach((field) => {
      const value = formData.get(field);
      if (value && typeof value === 'string') {
        feedback[field] = value;
      }
    });

    return feedback;
  }
}
