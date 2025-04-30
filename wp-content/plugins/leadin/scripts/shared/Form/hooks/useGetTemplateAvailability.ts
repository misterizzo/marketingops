import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import { ProxyMessages } from '../../../iframe/integratedMessages';
import {
  TemplateAvailability,
  HubSpotFormTemplateAvailabilityKeys,
  TemplateLabels,
  TemplateValues,
  TemplateAvailabilityResponse,
  ExcludedTemplateAvailabilityKeys,
} from '../../types';

export default function useGetTemplateAvailability() {
  const proxy = usePostAsyncBackgroundMessage();
  const [
    templateAvailability,
    setTemplateAvailability,
  ] = useState<TemplateAvailability | null>(null);

  const [availabilityPromise] = useState(
    () =>
      new Promise<TemplateAvailabilityResponse>(resolve => {
        proxy({
          key: ProxyMessages.GetTemplateAvailability,
          payload: {},
        }).then(data => {
          setTemplateAvailability(data.templateAvailability);
          resolve(data);
        });
      })
  );

  return { templateAvailability, availabilityPromise };
}

export const getTemplateOptions = (
  templateAvailability: TemplateAvailability | null
) => {
  if (!templateAvailability) {
    return {};
  }

  return {
    label: __('Templates', 'leadin'),
    options: Object.keys(templateAvailability)
      .filter(templateId => {
        const hubspotFormTemplateAvailability =
          templateAvailability[templateId as keyof TemplateAvailability];
        return (
          (hubspotFormTemplateAvailability.canCreateWithMissingScopes ||
            !hubspotFormTemplateAvailability.missingScopes.length) &&
          !Object.values(ExcludedTemplateAvailabilityKeys).includes(
            templateId as ExcludedTemplateAvailabilityKeys
          )
        );
      })
      .map(templateId => {
        return {
          label: __(
            TemplateLabels[templateId as keyof typeof TemplateLabels],
            'leadin'
          ),
          value: TemplateValues[templateId as keyof typeof TemplateValues],
        };
      }),
  };
};

export function isDefaultForm(value: HubSpotFormTemplateAvailabilityKeys) {
  return Object.values(TemplateValues).includes(value);
}
