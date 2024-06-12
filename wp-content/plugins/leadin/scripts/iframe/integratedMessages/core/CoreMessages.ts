export const CoreMessages = {
  HandshakeReceive: 'INTEGRATED_APP_EMBEDDER_HANDSHAKE_RECEIVED',
  SendRefreshToken: 'INTEGRATED_APP_EMBEDDER_SEND_REFRESH_TOKEN',
  ReloadParentFrame: 'INTEGRATED_APP_EMBEDDER_RELOAD_PARENT_FRAME',
  RedirectParentFrame: 'INTEGRATED_APP_EMBEDDER_REDIRECT_PARENT_FRAME',
  SendLocale: 'INTEGRATED_APP_EMBEDDER_SEND_LOCALE',
  SendDeviceId: 'INTEGRATED_APP_EMBEDDER_SEND_DEVICE_ID',
  SendIntegratedAppConfig: 'INTEGRATED_APP_EMBEDDER_CONFIG',
} as const;

export type CoreMessageType = typeof CoreMessages[keyof typeof CoreMessages];
