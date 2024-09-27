interface KeyStringObject {
  [key: string]: string;
}

export type ContentEmbedDetails = {
  activated: boolean;
  installed: boolean;
  canActivate: boolean;
  canInstall: boolean;
  nonce: string;
};

export interface LeadinConfig {
  accountName: string;
  adminUrl: string;
  activationTime: string;
  connectionStatus?: 'Connected' | 'NotConnected';
  deviceId: string;
  didDisconnect: '1' | '0';
  env: string;
  formsScript: string;
  meetingsScript: string;
  formsScriptPayload: string;
  hublet: string;
  hubspotBaseUrl: string;
  hubspotNonce: string;
  iframeUrl: string;
  impactLink?: string;
  lastAuthorizeTime: string;
  lastDeauthorizeTime: string;
  lastDisconnectTime: string;
  leadinPluginVersion: string;
  leadinQueryParams: KeyStringObject;
  loginUrl: string;
  locale: string;
  phpVersion: string;
  pluginPath: string;
  plugins: KeyStringObject;
  portalDomain: string;
  portalEmail: string;
  portalId: number;
  redirectNonce: string;
  restNonce: string;
  restUrl: string;
  reviewSkippedDate: string;
  refreshToken?: string;
  theme: string;
  trackConsent?: boolean | string;
  wpVersion: string;
  contentEmbed: ContentEmbedDetails;
  requiresContentEmbedScope?: boolean;
  decryptError?: string;
}

const {
  accountName,
  adminUrl,
  activationTime,
  connectionStatus,
  deviceId,
  didDisconnect,
  env,
  formsScript,
  meetingsScript,
  formsScriptPayload,
  hublet,
  hubspotBaseUrl,
  hubspotNonce,
  iframeUrl,
  impactLink,
  lastAuthorizeTime,
  lastDeauthorizeTime,
  lastDisconnectTime,
  leadinPluginVersion,
  leadinQueryParams,
  locale,
  loginUrl,
  phpVersion,
  pluginPath,
  plugins,
  portalDomain,
  portalEmail,
  portalId,
  redirectNonce,
  restNonce,
  restUrl,
  refreshToken,
  reviewSkippedDate,
  theme,
  trackConsent,
  wpVersion,
  contentEmbed,
  requiresContentEmbedScope,
  decryptError,
}: //@ts-expect-error global
LeadinConfig = window.leadinConfig;

export {
  accountName,
  adminUrl,
  activationTime,
  connectionStatus,
  deviceId,
  didDisconnect,
  env,
  formsScript,
  meetingsScript,
  formsScriptPayload,
  hublet,
  hubspotBaseUrl,
  hubspotNonce,
  iframeUrl,
  impactLink,
  lastAuthorizeTime,
  lastDeauthorizeTime,
  lastDisconnectTime,
  leadinPluginVersion,
  leadinQueryParams,
  loginUrl,
  locale,
  phpVersion,
  pluginPath,
  plugins,
  portalDomain,
  portalEmail,
  portalId,
  redirectNonce,
  restNonce,
  restUrl,
  refreshToken,
  reviewSkippedDate,
  theme,
  trackConsent,
  wpVersion,
  contentEmbed,
  requiresContentEmbedScope,
  decryptError,
};
