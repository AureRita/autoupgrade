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
import SendErrorReportDialog from '../dialogs/SendErrorReportDialog';

import DomLifecycle from '../types/DomLifecycle';
import { RoutesMatching } from '../types/scriptHandlerTypes';
import { routeHandler } from '../autoUpgrade';

export default class ScriptHandler {
  #currentScript: DomLifecycle | undefined;

  /**
   * @private
   * @type {RoutesMatching}
   * @description Maps route names to their corresponding page classes.
   */
  readonly #routesMatching: RoutesMatching = {
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

    'restore-backup-dialog': RestoreBackupDialog,
    'delete-backup-dialog': DeleteBackupDialog,
    'start-update-dialog': StartUpdateDialog,
    'send-error-report-dialog': SendErrorReportDialog
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
   * @param {string} scriptID - The ID of the route to load his associated script.
   * @returns void
   * @description Loads and mounts the page script associated with the specified route name.
   */
  loadScript(scriptID: string) {
    const classScript = this.#routesMatching[scriptID];
    if (classScript) {
      try {
        this.#currentScript = new classScript();
        this.#currentScript.mount();
      } catch (error) {
        console.error(`Failed to load script with ID ${scriptID}:`, error);
      }
    } else {
      console.debug(`No matching class found for ID: ${scriptID}`);
    }
  }

  /**
   * @public
   * @param {string} newRoute - The name of the route to load his associated script.
   * @returns void
   * @description Updates the currently loaded route script by destroying the current
   *              page instance and loading a new one based on the provided route name.
   */
  public updateRouteScript(newRoute: string) {
    this.#currentScript?.beforeDestroy();
    this.loadScript(newRoute);
  }

  /**
   * @public
   * @returns void
   * @description Unloads the currently loaded script.
   *  Should be called before updating the DOM.
   */
  public unloadRouteScript(): void {
    this.#currentScript?.beforeDestroy();
    this.#currentScript = undefined;
  }
}
