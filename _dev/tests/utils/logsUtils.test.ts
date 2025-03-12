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
import { parseLogWithSeverity } from '../../src/ts/appUI/utils/logsUtils';
import { Severity, LogEntry } from '../../src/ts/appUI/types/logsTypes';

describe('parseLogWithSeverity', () => {
  it('should parse a log with SUCCESS severity', () => {
    const log = 'DEBUG - Operation completed successfully';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.SUCCESS,
      message: 'Operation completed successfully'
    });
  });

  it('should parse a log with SUCCESS severity on multi-line', () => {
    const log = `DEBUG - Migration file: 8.1.7-catchup, Query: /* 8.0.0 */
DROP TABLE IF EXISTS \`ps_attribute_impact\``;
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.SUCCESS,
      message:
        'Migration file: 8.1.7-catchup, Query: /* 8.0.0 */\nDROP TABLE IF EXISTS `ps_attribute_impact`'
    });
  });

  it('should parse a log with WARNING severity', () => {
    const log = 'WARNING - Disk space is low';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.WARNING,
      message: 'Disk space is low'
    });
  });

  it('should parse a log with ERROR severity', () => {
    const log = 'ERROR - System failure occurred';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.ERROR,
      message: 'System failure occurred'
    });
  });

  it('should return ERROR severity for invalid log formats', () => {
    const log = 'This is an invalid log format';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.ERROR,
      message: 'This is an invalid log format'
    });
  });

  it('should handle logs with extra spaces around severity', () => {
    const log = '  INFO   -   Operation completed  ';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.SUCCESS,
      message: 'Operation completed'
    });
  });

  it('should handle empty log strings gracefully', () => {
    const log = '';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: Severity.ERROR,
      message: ''
    });
  });
});
