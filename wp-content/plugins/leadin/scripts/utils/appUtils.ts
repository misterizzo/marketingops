import $ from 'jquery';
import Raven, { configureRaven } from '../lib/Raven';

export function initApp(initFn: Function) {
  configureRaven();
  Raven.context(initFn);
}

export function initAppOnReady(initFn: (...args: any[]) => void) {
  function main() {
    $(initFn);
  }
  initApp(main);
}
