import { createContext, useContext } from 'react';
import {
  deviceId,
  hubspotBaseUrl,
  locale,
  portalId,
} from '../constants/leadinConfig';
import { Message } from './messageMiddleware';

export const BackgroudAppContext = createContext<any>(null);

export function useBackgroundAppContext() {
  return useContext(BackgroudAppContext);
}

export function usePostBackgroundMessage() {
  const app = useBackgroundAppContext();

  return (message: Message) => {
    app.postMessage(message);
  };
}

export function usePostAsyncBackgroundMessage(): (
  message: Message
) => Promise<any> {
  const app = useBackgroundAppContext();
  return (message: Message) => app.postAsyncMessage(message);
}
