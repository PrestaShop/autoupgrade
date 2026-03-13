/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
