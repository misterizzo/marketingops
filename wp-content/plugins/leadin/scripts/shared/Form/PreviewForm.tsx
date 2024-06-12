import React, { useEffect, useRef } from 'react';
import UIOverlay from '../UIComponents/UIOverlay';
import { formsScriptPayload, hublet } from '../../constants/leadinConfig';
import useFormScript from './hooks/useFormsScript';

export default function PreviewForm({
  portalId,
  formId,
}: {
  portalId: number;
  formId: string;
}) {
  const inputEl = useRef<HTMLDivElement>(null);
  const ready = useFormScript();

  useEffect(() => {
    if (!ready) {
      return;
    }
    if (inputEl.current) {
      inputEl.current.innerHTML = '';
      const embedScript = document.createElement('script');
      embedScript.innerHTML = `hbspt.forms.create({ portalId: '${portalId}', formId: '${formId}', region: '${hublet}', ${formsScriptPayload} });`;
      inputEl.current.appendChild(embedScript);
    }
  }, [formId, portalId, ready, inputEl]);

  return <UIOverlay ref={inputEl} />;
}
