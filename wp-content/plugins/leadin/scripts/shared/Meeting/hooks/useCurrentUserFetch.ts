import { useEffect, useState } from 'react';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import LoadState, { LoadStateType } from '../../enums/loadState';
import { ProxyMessages } from '../../../iframe/integratedMessages';

let user: any = null;

export default function useCurrentUserFetch() {
  const proxy = usePostAsyncBackgroundMessage();
  const [loadState, setLoadState] = useState<LoadStateType>(
    LoadState.NotLoaded
  );
  const [error, setError] = useState<null | Error>(null);

  const createUser = () => {
    if (!user) {
      setLoadState(LoadState.NotLoaded);
    }
  };

  const reload = () => {
    user = null;
    setLoadState(LoadState.NotLoaded);
    setError(null);
  };

  useEffect(() => {
    if (loadState === LoadState.NotLoaded && !user) {
      setLoadState(LoadState.Loading);
      proxy({
        key: ProxyMessages.FetchOrCreateMeetingUser,
      })
        .then(data => {
          user = data;
          setLoadState(LoadState.Idle);
        })
        .catch(err => {
          setError(err);
          setLoadState(LoadState.Failed);
        });
    }
  }, [loadState]);

  return { user, loadUserState: loadState, error, createUser, reload };
}
