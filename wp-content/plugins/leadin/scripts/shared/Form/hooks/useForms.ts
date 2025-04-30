import { useState } from 'react';
import debounce from 'lodash/debounce';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import { ProxyMessages } from '../../../iframe/integratedMessages';
import { IForm } from '../../types';
import useGetTemplateAvailability, {
  getTemplateOptions,
} from './useGetTemplateAvailability';

export default function useForms() {
  const proxy = usePostAsyncBackgroundMessage();
  const [formApiError, setFormApiError] = useState<any>(null);
  const { availabilityPromise } = useGetTemplateAvailability();

  const search = debounce(
    (search: string, callback: Function) => {
      return Promise.all([
        availabilityPromise,
        proxy({
          key: ProxyMessages.FetchForms,
          payload: {
            search,
          },
        }),
      ])
        .then(([templateAvailabilityResponse, forms]) => {
          const TEMPLATE_OPTIONS = getTemplateOptions(
            templateAvailabilityResponse.templateAvailability
          );

          callback([
            ...forms.map((form: IForm) => ({
              label: form.name,
              value: form.guid,
              embedVersion: form.embedVersion,
            })),
            TEMPLATE_OPTIONS,
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
