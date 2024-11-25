import {
  deviceId,
  hubspotBaseUrl,
  locale,
  portalId,
  leadinPluginVersion,
} from '../constants/leadinConfig';
import { initApp } from './appUtils';

type CallbackFn = (...args: any[]) => void;

export function initBackgroundApp(initFn: CallbackFn | CallbackFn[]) {
  function main() {
    if (Array.isArray(initFn)) {
      initFn.forEach(callback => callback());
    } else {
      initFn();
    }
  }
  initApp(main);
}

const getLeadinConfig = () => {
  return {
    leadinPluginVersion,
  };
};

export const getOrCreateBackgroundApp = (refreshToken = '') => {
  if ((window as any).LeadinBackgroundApp) {
    return (window as any).LeadinBackgroundApp;
  }
  const { IntegratedAppEmbedder, IntegratedAppOptions }: any = window;
  const options = new IntegratedAppOptions()
    .setLocale(locale)
    .setDeviceId(deviceId)
    .setLeadinConfig(getLeadinConfig())
    .setRefreshToken(refreshToken);

  const embedder = new IntegratedAppEmbedder(
    'integrated-plugin-proxy',
    portalId,
    hubspotBaseUrl,
    () => {}
  ).setOptions(options);

  embedder.attachTo(document.body, false);
  embedder.postStartAppMessage(); // lets the app know all all data has been passed to it

  (window as any).LeadinBackgroundApp = embedder;
  return (window as any).LeadinBackgroundApp;
};
