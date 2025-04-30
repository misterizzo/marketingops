import React, { useEffect, useRef } from 'react';
import UIOverlay from '../UIComponents/UIOverlay';
import {
  formsScriptPayload,
  hublet as region,
} from '../../constants/leadinConfig';
import PreviewDisabled from '../Common/PreviewDisabled';

export default function PreviewForm({
  portalId,
  formId,
  fullSiteEditor,
  embedVersion,
}: {
  portalId: number;
  formId: string;
  fullSiteEditor?: boolean;
  embedVersion?: string;
}) {
  const isFormV4 = embedVersion === 'v4';

  const inputEl = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (inputEl.current) {
      //@ts-expect-error Hubspot global
      const hbspt = window.parent.hbspt || window.hbspt;
      inputEl.current.innerHTML = '';
      const isQa = formsScriptPayload.includes('qa');
      if (isFormV4) {
        const container = document.createElement('div');
        container.classList.add('hs-form-frame');
        container.dataset.region = region;
        container.dataset.formId = formId;
        container.dataset.portalId = portalId.toString();
        container.dataset.env = isQa ? 'qa' : '';
        inputEl.current.appendChild(container);
      } else {
        const additionalParams = isQa ? { env: 'qa' } : {};

        hbspt.forms.create({
          portalId,
          formId,
          region,
          target: `#${inputEl.current.id}`,
          ...additionalParams,
        });
      }
    }
  }, [formId, portalId, inputEl, isFormV4]);

  if (fullSiteEditor) {
    return <PreviewDisabled />;
  }

  return <UIOverlay ref={inputEl} id={`hbspt-previewform-${formId}`} />;
}
