import {
  // Import utils
  testContext,
} from '@prestashop-core/ui-testing';

import {createConnection} from 'mysql2/promise';
import type {Connection} from 'mysql2/promise';

import {
  test, expect, Page, BrowserContext,
} from '@playwright/test';

let dbConnection: Connection;

const baseContext: string = 'shopVersion_checkVersionIndatabase';
const psVersion = testContext.getPSVersion();

test.describe('Check new shop version', () => {
  const dbPrefix: string = global.INSTALL.DB_PREFIX;

  test.beforeAll(async () => {
    if (!global.GENERATE_FAILED_STEPS) {
      dbConnection = await createConnection({
        user: global.INSTALL.DB_USER,
        password: global.INSTALL.DB_PASSWD,
        host: 'localhost',
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
    await testContext.addContextItem(test.info(), 'testIdentifier', 'checkPsVersion', baseContext);

    await dbConnection.execute(`TRUNCATE TABLE ${dbPrefix}stock_mvt`);

    await dbConnection.query(`SELECT PS_VERSION_DB FROM ${dbPrefix}configuration`, (result:string) => {
      expect(result).toEqual(psVersion);
    });
  });
});
