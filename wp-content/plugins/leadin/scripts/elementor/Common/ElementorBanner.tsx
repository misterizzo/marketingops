import React from 'react';

interface IElementorBannerProps {
  type?: string;
}

export default function ElementorBanner({
  type = 'warning',
  children,
}: React.PropsWithChildren<IElementorBannerProps>) {
  return (
    <div className="elementor-control-content">
      <div
        className={`elementor-control-raw-html elementor-panel-alert elementor-panel-alert-${type}`}
      >
        {children}
      </div>
    </div>
  );
}
