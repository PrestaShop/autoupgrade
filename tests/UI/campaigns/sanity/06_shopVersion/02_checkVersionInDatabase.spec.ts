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
import {
  // Import utils
  utilsTest,
} from '@prestashop-core/ui-testing';

import {createConnection} from 'mysql2/promise';
import type {Connection, FieldPacket, RowDataPacket} from 'mysql2/promise';

import {
  test, expect,
} from '@playwright/test';

let dbConnection: Connection;

const psVersion = utilsTest.getPSVersion();

test.describe('Check new shop version', () => {
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
      `SELECT value FROM ${dbPrefix}configuration WHERE name = 'PS_VERSION_DB'`,
    );
    expect(resultRows[0].value).toContain(psVersion);
  });
});
