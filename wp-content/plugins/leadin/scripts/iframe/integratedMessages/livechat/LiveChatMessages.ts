export const LiveChatMessages = {
  CreateLiveChatAppNavigation: 'CREATE_LIVE_CHAT_APP_NAVIGATION',
} as const;

export type LiveChatMessageType = typeof LiveChatMessages[keyof typeof LiveChatMessages];
