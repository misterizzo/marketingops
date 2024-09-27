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
}: {
  portalId: number;
  formId: string;
  fullSiteEditor?: boolean;
}) {
  const inputEl = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (inputEl.current) {
      //@ts-expect-error Hubspot global
      const hbspt = window.parent.hbspt || window.hbspt;

      const additionalParams = formsScriptPayload.includes('qa')
        ? { env: 'qa' }
        : {};
      hbspt.forms.create({
        portalId,
        formId,
        region,
        target: `#${inputEl.current.id}`,
        ...additionalParams,
      });
    }
  }, [formId, portalId, inputEl]);

  if (fullSiteEditor) {
    return <PreviewDisabled />;
  }

  return <UIOverlay ref={inputEl} id={`hbspt-previewform-${formId}`} />;
}
