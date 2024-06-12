import * as Core from './core/CoreMessages';
import * as Forms from './forms/FormsMessages';
import * as LiveChat from './livechat/LiveChatMessages';
import * as Plugin from './plugin/PluginMessages';
import * as Proxy from './proxy/ProxyMessages';

export type MessageType =
  | Core.CoreMessageType
  | Forms.FormMessageType
  | LiveChat.LiveChatMessageType
  | Plugin.PluginMessageType
  | Proxy.ProxyMessageType;

export * from './core/CoreMessages';
export * from './forms/FormsMessages';
export * from './livechat/LiveChatMessages';
export * from './plugin/PluginMessages';
export * from './proxy/ProxyMessages';
