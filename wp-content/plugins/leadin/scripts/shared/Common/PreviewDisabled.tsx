import React from 'react';
import UIContainer from '../UIComponents/UIContainer';
import HubspotWrapper from './HubspotWrapper';
import { pluginPath } from '../../constants/leadinConfig';
import { __ } from '@wordpress/i18n';

export default function PreviewDisabled() {
  const errorHeader = __('Preview Unavailable', 'leadin');
  const errorMessage = `${__(
    'This block cannot be previewed within the Full Site Editor',
    'leadin'
  )} ${__('Switch to the Block Editor to view the content', 'leadin')}`;

  return (
    <HubspotWrapper pluginPath={pluginPath}>
      <UIContainer textAlign="center">
        <h4>{errorHeader}</h4>
        <p>
          <b>{errorMessage}</b>
        </p>
      </UIContainer>
    </HubspotWrapper>
  );
}
