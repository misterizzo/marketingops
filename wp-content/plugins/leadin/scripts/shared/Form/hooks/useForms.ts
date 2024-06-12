import { useState } from 'react';
import debounce from 'lodash/debounce';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import { DEFAULT_OPTIONS } from '../../../constants/defaultFormOptions';
import { ProxyMessages } from '../../../iframe/integratedMessages';
import { IForm } from '../../types';

export default function useForms() {
  const proxy = usePostAsyncBackgroundMessage();
  const [formApiError, setFormApiError] = useState<any>(null);

  const search = debounce(
    (search: string, callback: Function) => {
      return proxy({
        key: ProxyMessages.FetchForms,
        payload: {
          search,
        },
      })
        .then(forms => {
          callback([
            ...forms.map((form: IForm) => ({
              label: form.name,
              value: form.guid,
            })),
            DEFAULT_OPTIONS,
          ]);
        })
        .catch(error => {
          setFormApiError(error);
        });
    },
    300,
    { trailing: true }
  );

  return {
    search,
    formApiError,
    reset: () => setFormApiError(null),
  };
}
