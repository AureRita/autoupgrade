import * as Sentry from '@sentry/browser';
import { SeverityLevel } from '@sentry/browser';
import { maskSensitiveInfoInUrl } from '../utils/urlUtils';
import { Feedback, Logs, LogsFields } from '../types/sentryApi';

const adminDir = window.AutoUpgradeVariables.admin_dir;
const feedbackModalTag = 'feedbackModal';

Sentry.init({
  dsn: 'https://eae192966a8d79509154c65c317a7e5d@o298402.ingest.us.sentry.io/4507254110552064',
  release: `v${window.AutoUpgradeVariables.module_version}`,
  sendDefaultPii: false,
  beforeSend(event) {
    if (event.tags?.source !== feedbackModalTag) {
      return null;
    }

    if (event.request?.url) {
      event.request.url = maskSensitiveInfoInUrl(window.location.href, adminDir);
    }

    return event;
  },
  beforeBreadcrumb(breadcrumb) {
    ['url', 'from', 'to'].forEach((key) => {
      if (breadcrumb.data?.[key]) {
        breadcrumb.data[key] = maskSensitiveInfoInUrl(breadcrumb.data[key], adminDir);
      }
    });

    return breadcrumb;
  }
});

/**
 * Sends enriched user feedback to Sentry with optional logs and metadata.
 * This function attaches log files, captures a custom event, and optionally sends user feedback with an associated event ID.
 *
 * @param {string} message - The message to describe the feedback or error.
 * @param {Logs} [logs={}] - An object containing optional logs, warnings, and errors to attach.
 * @param {Feedback} [feedback={}] - An object containing optional user feedback fields such as email and comments.
 * @param {SeverityLevel} [level='error'] - The severity level of the event (e.g., 'info', 'warning', 'error').
 */
export function sendUserFeedback(
  message: string,
  logs: Logs = {},
  feedback: Feedback = {},
  level: SeverityLevel = 'error'
) {
  const attachments: { key: LogsFields; filename: string }[] = [
    { key: LogsFields.LOGS, filename: 'logs.txt' },
    { key: LogsFields.WARNINGS, filename: 'summary_warnings.txt' },
    { key: LogsFields.ERRORS, filename: 'summary_errors.txt' }
  ];

  attachments.forEach(({ key, filename }) => {
    if (logs[key]) {
      Sentry.getCurrentScope().addAttachment({
        filename,
        data: logs[key],
        contentType: 'text/plain'
      });
    }
  });

  const maskedUrl = maskSensitiveInfoInUrl(window.location.href, adminDir);

  const eventId = Sentry.captureEvent({
    message,
    level,
    tags: {
      url: maskedUrl,
      source: feedbackModalTag,
      phpVersion: window.AutoUpgradeVariables.php_version,
      anonymousId: window.AutoUpgradeVariables.anonymous_id
    }
  });

  if (feedback.email || feedback.comments) {
    Sentry.captureFeedback(
      {
        associatedEventId: eventId,
        email: feedback.email,
        message: feedback.comments ?? ''
      },
      {
        captureContext: {
          tags: {
            url: maskedUrl,
            source: feedbackModalTag,
            phpVersion: window.AutoUpgradeVariables.php_version,
            anonymousId: window.AutoUpgradeVariables.anonymous_id
          }
        }
      }
    );
  }

  Sentry.getCurrentScope().clearAttachments();
}
