import { useState, useEffect } from 'react';
import LoadState, { LoadStateType } from '../../../shared/enums/loadState';
import { ProxyMessages } from '../../../iframe/integratedMessages';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import { IForm } from '../../../shared/types';

interface FormOption {
  label: string;
  value: string;
}

export default function useForms() {
  const proxy = usePostAsyncBackgroundMessage();
  const [loadState, setLoadState] = useState<LoadStateType>(
    LoadState.NotLoaded
  );
  const [hasError, setError] = useState(null);
  const [forms, setForms] = useState<FormOption[]>([]);

  useEffect(() => {
    if (loadState === LoadState.NotLoaded) {
      proxy({
        key: ProxyMessages.FetchForms,
        payload: {
          search: '',
        },
      })
        .then(data => {
          setForms(
            data.map((form: IForm) => ({
              label: form.name,
              value: form.guid,
            }))
          );
          setLoadState(LoadState.Loaded);
        })
        .catch(error => {
          setError(error);
          setLoadState(LoadState.Failed);
        });
    }
  }, [loadState]);

  return { forms, loading: loadState === LoadState.Loading, hasError };
}
