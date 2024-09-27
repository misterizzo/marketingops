import { useEffect } from 'react';
import Raven from '../lib/Raven';

import {
  accountName,
  adminUrl,
  connectionStatus,
  deviceId,
  hubspotBaseUrl,
  leadinQueryParams,
  locale,
  plugins,
  portalDomain,
  portalEmail,
  portalId,
  reviewSkippedDate,
  refreshToken,
  impactLink,
  theme,
  lastAuthorizeTime,
  lastDeauthorizeTime,
  lastDisconnectTime,
  leadinPluginVersion,
  phpVersion,
  wpVersion,
  contentEmbed,
  requiresContentEmbedScope,
  decryptError,
  LeadinConfig,
} from '../constants/leadinConfig';
import { App, AppIframe } from './constants';
import { messageMiddleware } from './messageMiddleware';
import { resizeWindow, useIframeNotRendered } from '../utils/iframe';

type PartialLeadinConfig = Pick<
  LeadinConfig,
  | 'accountName'
  | 'adminUrl'
  | 'connectionStatus'
  | 'deviceId'
  | 'plugins'
  | 'portalDomain'
  | 'portalEmail'
  | 'portalId'
  | 'reviewSkippedDate'
  | 'refreshToken'
  | 'impactLink'
  | 'theme'
  | 'trackConsent'
  | 'lastAuthorizeTime'
  | 'lastDeauthorizeTime'
  | 'lastDisconnectTime'
  | 'leadinPluginVersion'
  | 'phpVersion'
  | 'wpVersion'
  | 'contentEmbed'
  | 'requiresContentEmbedScope'
  | 'decryptError'
>;

type AppIntegrationConfig = Pick<LeadinConfig, 'adminUrl'>;

const getIntegrationConfig = (): AppIntegrationConfig => {
  return {
    adminUrl: leadinQueryParams.adminUrl,
  };
};

/**
 * A modified version of the original leadinConfig that is passed to some integrated apps.
 *
 * Important:
 * Try not to add new fields here.
 * This config is already too large and broad in scope.
 * It tightly couples the apps that use it with the WordPress plugin.
 * Consider instead passing new required fields as new entry to PluginAppOptions or app-specific options.
 */
type AppLeadinConfig = {
  admin: string;
  company: string;
  email: string;
  firstName: string;
  irclickid: string;
  justConnected: string;
  lastName: string;
  mpid: string;
  nonce: string;
  websiteName: string;
} & PartialLeadinConfig;

const getLeadinConfig = (): AppLeadinConfig => {
  const utm_query_params = Object.keys(leadinQueryParams)
    .filter(x => /^utm/.test(x))
    .reduce(
      (p: { [key: string]: string }, c: string) => ({
        [c]: leadinQueryParams[c],
        ...p,
      }),
      {}
    );
  return {
    accountName,
    admin: leadinQueryParams.admin,
    adminUrl,
    company: leadinQueryParams.company,
    connectionStatus,
    deviceId,
    email: leadinQueryParams.email,
    firstName: leadinQueryParams.firstName,
    irclickid: leadinQueryParams.irclickid,
    justConnected: leadinQueryParams.justConnected,
    lastName: leadinQueryParams.lastName,
    lastAuthorizeTime,
    lastDeauthorizeTime,
    lastDisconnectTime,
    leadinPluginVersion,
    mpid: leadinQueryParams.mpid,
    nonce: leadinQueryParams.nonce,
    phpVersion,
    plugins,
    portalDomain,
    portalEmail,
    portalId,
    reviewSkippedDate,
    theme,
    trackConsent: leadinQueryParams.trackConsent,
    websiteName: leadinQueryParams.websiteName,
    wpVersion,
    contentEmbed,
    requiresContentEmbedScope,
    decryptError,
    ...utm_query_params,
  };
};

const getAppOptions = (app: App, createRoute = false) => {
  const {
    IntegratedAppOptions,
    FormsAppOptions,
    LiveChatAppOptions,
    PluginAppOptions,
  }: any = window;
  let options;
  switch (app) {
    case App.Plugin:
      options = new PluginAppOptions();
      break;
    case App.PluginSettings:
      options = new PluginAppOptions().setPluginSettingsInit();
      break;
    case App.Forms:
      options = new FormsAppOptions().setIntegratedAppConfig(
        getIntegrationConfig()
      );
      if (createRoute) {
        options = options.setCreateFormAppInit();
      }
      break;
    case App.LiveChat:
      options = new LiveChatAppOptions();
      if (createRoute) {
        options = options.setCreateLiveChatAppInit();
      }
      break;
    default:
      options = new IntegratedAppOptions();
  }

  return options;
};

export default function useAppEmbedder(
  app: App,
  createRoute: boolean,
  container: HTMLElement | null
) {
  console.info(
    'HubSpot plugin - starting app embedder for:',
    AppIframe[app],
    container
  );
  const iframeNotRendered = useIframeNotRendered(AppIframe[app]);

  useEffect(() => {
    const { IntegratedAppEmbedder }: any = window;

    if (IntegratedAppEmbedder) {
      const options = getAppOptions(app, createRoute)
        .setLocale(locale)
        .setDeviceId(deviceId)
        .setRefreshToken(refreshToken)
        .setLeadinConfig(getLeadinConfig());

      const embedder = new IntegratedAppEmbedder(
        AppIframe[app],
        portalId,
        hubspotBaseUrl,
        resizeWindow,
        refreshToken ? '' : impactLink
      ).setOptions(options);

      embedder.subscribe(messageMiddleware(embedder));
      embedder.attachTo(container, true);
      embedder.postStartAppMessage(); // lets the app know all all data has been passed to it

      (window as any).embedder = embedder;
    }
  }, []);

  if (iframeNotRendered) {
    console.error('HubSpot plugin Iframe not rendered', {
      portalId,
      container,
      appName: AppIframe[app],
      hasIntegratedAppEmbedder: !!(window as any).IntegratedAppEmbedder,
    });
    Raven.captureException(new Error('Leadin Iframe not rendered'), {
      fingerprint: ['USE_APP_EMBEDDER', 'IFRAME_SETUP_ERROR'],
      extra: {
        portalId,
        container,
        app,
        hubspotBaseUrl,
        impactLink,
        appName: AppIframe[app],
        hasRefreshToken: !!refreshToken,
      },
    });
  }

  return iframeNotRendered;
}
