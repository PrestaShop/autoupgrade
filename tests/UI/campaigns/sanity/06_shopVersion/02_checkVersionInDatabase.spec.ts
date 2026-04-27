/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import {createConnection} from 'mysql2/promise';
import type {Connection, FieldPacket, RowDataPacket} from 'mysql2/promise';

import {
  test, expect,
} from '@playwright/test';

let dbConnection: Connection;

test.describe('Check new shop version from the database', () => {
  const dbPrefix: string = global.INSTALL.DB_PREFIX;

  test.beforeAll(async () => {
    if (!global.GENERATE_FAILED_STEPS) {
      dbConnection = await createConnection({
        user: global.INSTALL.DB_USER,
        password: global.INSTALL.DB_PASSWD,
        host: global.INSTALL.DB_SERVER,
        port: 3306,
        database: global.INSTALL.DB_NAME,
        connectionLimit: 5,
      });
    }
  });
  test.afterAll(async () => {
    if (dbConnection) {
      await dbConnection.end();
    }
  });

  test('should check psVersion from the database', async () => {
    const [resultRows]: [RowDataPacket[], FieldPacket[]] = await dbConnection.query(
      `SELECT value
       FROM ${dbPrefix}configuration
       WHERE name = 'PS_VERSION_DB'`,
    );
    // @ts-ignore
    expect(resultRows[0].value).toContain(process.env.PS_VERSION.slice(0, 5));
  });
});
