import React, { Fragment, useEffect, useRef } from 'react';
import UIOverlay from '../UIComponents/UIOverlay';
import PreviewDisabled from '../Common/PreviewDisabled';

interface IPreviewForm {
  url: string;
  fullSiteEditor?: boolean;
}

export default function PreviewForm({ url, fullSiteEditor }: IPreviewForm) {
  const inputEl = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (inputEl.current) {
      //@ts-expect-error Hubspot global
      const hbspt = window.parent.hbspt || window.hbspt;
      hbspt.meetings.create('.meetings-iframe-container');
    }
  }, [url, inputEl]);

  if (fullSiteEditor) {
    return <PreviewDisabled />;
  }

  return (
    <Fragment>
      {url && (
        <UIOverlay
          ref={inputEl}
          className="meetings-iframe-container"
          data-src={`${url}?embed=true`}
        ></UIOverlay>
      )}
    </Fragment>
  );
}
