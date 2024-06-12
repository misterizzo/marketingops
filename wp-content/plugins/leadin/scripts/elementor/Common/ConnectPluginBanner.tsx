import React from 'react';
import ElementorBanner from './ElementorBanner';
import { __ } from '@wordpress/i18n';

export default function ConnectPluginBanner() {
  return (
    <ElementorBanner>
      <b
        dangerouslySetInnerHTML={{
          __html: __(
            'The HubSpot plugin is not connected right now To use HubSpot tools on your WordPress site, %1$sconnect the plugin now%2$s'
          )
            .replace(
              '%1$s',
              '<a class="leadin-banner__link" href="admin.php?page=leadin&bannerClick=true">'
            )
            .replace('%2$s', '</a>'),
        }}
      ></b>
    </ElementorBanner>
  );
}
