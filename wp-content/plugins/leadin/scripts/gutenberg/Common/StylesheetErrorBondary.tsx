import React from 'react';
import { leadinPluginVersion, pluginPath } from '../../constants/leadinConfig';
import { useRefEffect } from '@wordpress/compose';

function StylesheetErrorBondary({ children }: React.PropsWithChildren) {
  const ref = useRefEffect(element => {
    const { ownerDocument } = element;
    if (
      ownerDocument &&
      !ownerDocument.getElementById('leadin-gutenberg-css')
    ) {
      const link = ownerDocument.createElement('link');
      link.id = 'leadin-gutenberg-css';
      link.rel = 'stylesheet';
      link.href = `${pluginPath}/build/gutenberg.css?ver=${leadinPluginVersion}`;
      ownerDocument.head.appendChild(link);
    }
  }, []);

  return <div ref={ref}>{children}</div>;
}

export default StylesheetErrorBondary;
