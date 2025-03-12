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
import { Log, Severity } from '../types/logsTypes';
import StoreAbstract from './StoreAbstract';

class LogStore extends StoreAbstract<Log[]> {
  #logs: Log[] = [];

  addLog(log: Log): void {
    this.#logs.push(log);
    this.notify(this.#logs);
  }

  getLogs(): Log[] {
    return this.#logs;
  }

  getLog(index: number): Log {
    return this.#logs[index];
  }

  getLogsLength(): number {
    return this.#logs.length;
  }

  getWarnings(): Log[] {
    return this.#logs.filter((log) => log.severity === Severity.WARNING);
  }

  getErrors(): Log[] {
    return this.#logs.filter((log) => log.severity === Severity.ERROR);
  }

  clearLogs(): void {
    this.#logs = [];
    this.notify(this.#logs);
  }
}

export const logStore = new LogStore();
