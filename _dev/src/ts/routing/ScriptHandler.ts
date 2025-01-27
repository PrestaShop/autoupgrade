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
import HomePage from '../pages/HomePage';
import UpdatePageVersionChoice from '../pages/UpdatePageVersionChoice';
import UpdatePageUpdateOptions from '../pages/UpdatePageUpdateOptions';
import UpdatePageBackupOptions from '../pages/UpdatePageBackupOptions';
import UpdatePageBackup from '../pages/UpdatePageBackup';
import UpdatePageUpdate from '../pages/UpdatePageUpdate';
import UpdatePagePostUpdate from '../pages/UpdatePagePostUpdate';

import RestorePageBackupSelection from '../pages/RestorePageBackupSelection';
import RestorePageRestore from '../pages/RestorePageRestore';
import RestorePagePostRestore from '../pages/RestorePagePostRestore';

import RestoreBackupDialog from '../dialogs/RestoreBackupDialog';
import DeleteBackupDialog from '../dialogs/DeleteBackupDialog';
import StartUpdateDialog from '../dialogs/StartUpdateDialog';
import StartBackupDialog from '../dialogs/StartBackupDialog';
import SendErrorReportDialog from '../dialogs/SendErrorReportDialog';

import { ScriptType, ScriptsMatching, CurrentScripts } from '../types/scriptHandlerTypes';
import { routeHandler } from '../autoUpgrade';
import ErrorPage from '../pages/ErrorPage';

export default class ScriptHandler {
  #currentScripts: CurrentScripts = {
    [ScriptType.PAGE]: undefined,
    [ScriptType.DIALOG]: undefined
  };

  /**
   * @private
   * @type {ScriptsMatching}
   * @description Map script names by script type to their corresponding script classes.
   */
  readonly #scriptsMatching: ScriptsMatching = {
    [ScriptType.PAGE]: {
      'home-page': HomePage,
      'update-page-version-choice': UpdatePageVersionChoice,
      'update-page-update-options': UpdatePageUpdateOptions,
      'update-page-backup-options': UpdatePageBackupOptions,
      'update-page-backup': UpdatePageBackup,
      'update-page-update': UpdatePageUpdate,
      'update-page-post-update': UpdatePagePostUpdate,

      'restore-page-backup-selection': RestorePageBackupSelection,
      'restore-page-restore': RestorePageRestore,
      'restore-page-post-restore': RestorePagePostRestore,

      'error-page': ErrorPage
    },
    [ScriptType.DIALOG]: {
      'restore-backup-dialog': RestoreBackupDialog,
      'delete-backup-dialog': DeleteBackupDialog,
      'start-update-dialog': StartUpdateDialog,
      'start-backup-dialog': StartBackupDialog,
      'send-error-report-dialog': SendErrorReportDialog
    }
  };

  /**
   * @constructor
   * @description Initializes the `ScriptHandler` by loading the page script associated with the current route.
   */
  constructor() {
    const currentRoute = routeHandler.getCurrentRoute();

    if (currentRoute) {
      this.loadScript(currentRoute);
    }
  }

  /**
   * @public
   * @param {string} scriptID - The ID of the script to load.
   * @returns void
   * @description Loads and mounts the script associated with the specified script name.
   */
  public loadScript(scriptID: string): void {
    const scriptType = this.#getScriptTypeByScriptID(scriptID);

    if (!scriptType) {
      console.debug(`No matching class found for ID: ${scriptID}`);
      // Outside a hydration, the scriptID matches the route query param.
      // If it does not exist, we load the error management script instead.
      if (!this.#currentScripts[ScriptType.PAGE]) {
        this.loadScript('error-page');
      }
      return;
    }

    const classScript = this.#scriptsMatching[scriptType][scriptID];

    try {
      if (this.#currentScripts[scriptType] !== undefined) {
        this.unloadScriptType(scriptType);
      }
      this.#currentScripts[scriptType] = new classScript();
      this.#currentScripts[scriptType].mount();
    } catch (error) {
      console.error(`Failed to load script with ID ${scriptID}:`, error);
    }
  }

  /**
   * @public
   * @param {ScriptType} scriptType - The type of the script to unload his associated script.
   * @returns void
   * @description Unloads the currently loaded script from his type.
   *  Should be called before updating the DOM.
   */
  public unloadScriptType(scriptType: ScriptType): void {
    this.#currentScripts[scriptType]?.beforeDestroy();
    this.#currentScripts[scriptType] = undefined;
  }

  #getScriptTypeByScriptID(scriptID: string): ScriptType | null {
    let scriptType = null;
    const scriptTypeKeys = Object.values(ScriptType);
    scriptTypeKeys.forEach((key) => {
      const type = ScriptType[key];
      if (this.#scriptsMatching[type][scriptID]) {
        scriptType = type;
      }
    });

    if (!scriptType) {
      console.debug(`No matching script in script types found for script with ID: ${scriptID}`);
    }

    return scriptType;
  }
}
