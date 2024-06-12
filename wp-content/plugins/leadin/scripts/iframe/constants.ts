export enum App {
  Forms,
  LiveChat,
  Plugin,
  PluginSettings,
  Background,
}

export const AppIframe = {
  [App.Forms]: 'integrated-form-app',
  [App.LiveChat]: 'integrated-livechat-app',
  [App.Plugin]: 'integrated-plugin-app',
  [App.PluginSettings]: 'integrated-plugin-app',
  [App.Background]: 'integrated-plugin-proxy',
} as const;
