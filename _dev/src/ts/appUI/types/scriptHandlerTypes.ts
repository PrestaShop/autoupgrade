/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DomLifecycle from '../../types/DomLifecycle';

export enum ScriptType {
  PAGE = 'PAGE',
  DIALOG = 'DIALOG'
}

type CurrentScripts = {
  [key in ScriptType]: undefined | DomLifecycle;
};

type ScriptsMatching = {
  [key in ScriptType]: {
    [key: string]: new () => DomLifecycle;
  };
};

export type { ScriptsMatching, CurrentScripts };
