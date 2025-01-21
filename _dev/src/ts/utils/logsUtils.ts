import {
  ErrorSeverity,
  LogEntry,
  LogsSeverity,
  Severity,
  SuccessSeverity,
  WarningSeverity
} from '../types/logsTypes';
import type { Procedure } from '../types/logsUtilsTypes';
import type { Log } from '../types/logsTypes';

/**
 * @public
 * @type {Record<LogsSeverity, Severity>}
 * @description Maps severity levels to their corresponding CSS classes for styling and process purposes.
 */
export const severityToClassMap: Record<LogsSeverity, Severity> = {
  ...Object.fromEntries(Object.values(SuccessSeverity).map((s) => [s, Severity.SUCCESS])),
  ...Object.fromEntries(Object.values(WarningSeverity).map((s) => [s, Severity.WARNING])),
  ...Object.fromEntries(Object.values(ErrorSeverity).map((s) => [s, Severity.ERROR]))
} as Record<LogsSeverity, Severity>;

const severityPattern = [
  ...Object.values(SuccessSeverity),
  ...Object.values(WarningSeverity),
  ...Object.values(ErrorSeverity)
].join('|');

/**
 * @public
 * @param {string} log - The log string to be parsed. Should be in the format: "SEVERITY - Message".
 * @returns {LogEntry} An object containing the severity class and log message.
 * @description Parses a log string to extract its severity and corresponding CSS class for styling.
 */
export function parseLogWithSeverity(log: string): LogEntry {
  const logTrimed = log.trim();
  const severityRegex = new RegExp(`^(${severityPattern})\\s*-\\s*(.*)$`, 's');
  const match = severityRegex.exec(logTrimed);

  if (match) {
    const severityStr = match[1] as LogsSeverity;
    const message = match[2];
    const severity = severityToClassMap[severityStr] || Severity.ERROR;

    return { severity, message };
  }

  return { severity: Severity.ERROR, message: log };
}

/**
 * @public
 * @template T
 * @param {T} func - The function to debounce.
 * @param {number} wait - The delay in milliseconds before the function is executed.
 * @return {(...args: Parameters<T>) => void & { clear: () => void }} - A debounced function
 * that delays the execution of `func` and provides a `clear` method to cancel any pending execution.
 * @description Creates a debounced version of the given function, ensuring it is executed
 * only after the specified delay has elapsed since the last invocation.
 * The returned function also includes a `clear` method to cancel any pending executions.
 */
export function debounce<T extends Procedure>(
  func: T,
  wait: number
): { debounced: (...args: Parameters<T>) => void; cancel: () => void } {
  let timeoutId: ReturnType<typeof setTimeout> | undefined;

  const debounced = (...args: Parameters<T>): void => {
    if (timeoutId) {
      clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
      func(...args);
    }, wait);
  };

  const cancel = (): void => {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = undefined;
    }
  };

  return { debounced, cancel };
}

/**
 * @public
 * @param logs
 * @description
 */
export function formatLogsMessages(logs: Log[]): string {
  return logs.map((log) => log.message).join('\n');
}
