/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
export enum SuccessSeverity {
  DEBUG = 'DEBUG',
  INFO = 'INFO',
  NOTICE = 'NOTICE'
}

export enum WarningSeverity {
  WARNING = 'WARNING'
}

export enum ErrorSeverity {
  ERROR = 'ERROR',
  CRITICAL = 'CRITICAL',
  ALERT = 'ALERT',
  EMERGENCY = 'EMERGENCY'
}

export type LogsSeverity = SuccessSeverity | WarningSeverity | ErrorSeverity;

export enum Severity {
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error'
}

export interface LogEntry {
  severity: Severity;
  message: string;
}

export interface Log extends LogEntry {
  height: number;
  offsetTop: number;
  HTMLElement?: HTMLDivElement;
}

export interface VisibleLogs {
  marginTop: number;
  marginBottom: number;
  visibleLogs: Log[];
}
