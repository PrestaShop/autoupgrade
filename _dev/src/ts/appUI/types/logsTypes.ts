/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
