import DomLifecycle from './DomLifecycle';

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
