import React, { Fragment, useEffect, useRef } from 'react';
import UIOverlay from '../UIComponents/UIOverlay';
import useMeetingsScript from './hooks/useMeetingsScript';

interface IPreviewForm {
  url: string;
}

export default function PreviewForm({ url }: IPreviewForm) {
  const ready = useMeetingsScript();
  const inputEl = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!ready) {
      return;
    }
    if (inputEl.current) {
      inputEl.current.innerHTML = '';
      const container = document.createElement('div');
      container.dataset.src = `${url}?embed=true`;
      container.classList.add('meetings-iframe-container');
      inputEl.current.appendChild(container);
      const embedScript = document.createElement('script');
      embedScript.innerHTML =
        'hbspt.meetings.create(".meetings-iframe-container");';
      inputEl.current.appendChild(embedScript);
    }
  }, [url, ready, inputEl]);

  return <Fragment>{url && <UIOverlay ref={inputEl}></UIOverlay>}</Fragment>;
}
