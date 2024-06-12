import { useState } from 'react';
import {
  usePostAsyncBackgroundMessage,
  usePostBackgroundMessage,
} from '../../../iframe/useBackgroundApp';
import { FormType } from '../../../constants/defaultFormOptions';
import LoadState, { LoadStateType } from '../../enums/loadState';
import { ProxyMessages } from '../../../iframe/integratedMessages';

export default function useCreateFormFromTemplate(origin = 'gutenberg') {
  const proxy = usePostAsyncBackgroundMessage();
  const track = usePostBackgroundMessage();
  const [loadState, setLoadState] = useState<LoadStateType>(LoadState.Idle);
  const [formApiError, setFormApiError] = useState<any>(null);

  const createFormByTemplate = (type: FormType) => {
    setLoadState(LoadState.Loading);
    track({
      key: ProxyMessages.TrackFormCreatedFromTemplate,
      payload: {
        type,
        origin,
      },
    });

    return proxy({
      key: ProxyMessages.CreateFormFromTemplate,
      payload: type,
    })
      .then(form => {
        setLoadState(LoadState.Idle);
        return form;
      })
      .catch(err => {
        setFormApiError(err);
        track({
          key: ProxyMessages.TrackFormCreationFailed,
          payload: {
            origin,
          },
        });
        setLoadState(LoadState.Failed);
      });
  };

  return {
    isCreating: loadState === LoadState.Loading,
    hasError: loadState === LoadState.Failed,
    formApiError,
    createFormByTemplate,
    reset: () => setLoadState(LoadState.Idle),
  };
}
