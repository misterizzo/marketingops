import React from 'react';
import UIButton from '../UIComponents/UIButton';
import UIContainer from '../UIComponents/UIContainer';
import HubspotWrapper from './HubspotWrapper';
import { adminUrl, redirectNonce } from '../../constants/leadinConfig';
import { pluginPath } from '../../constants/leadinConfig';
import { __ } from '@wordpress/i18n';

interface IErrorHandlerProps {
  status: number;
  resetErrorState?: React.MouseEventHandler<HTMLButtonElement>;
  errorInfo?: {
    header: string;
    message: string;
    action: string;
  };
}

function redirectToPlugin() {
  window.location.href = `${adminUrl}admin.php?page=leadin&leadin_expired=${redirectNonce}`;
}

export default function ErrorHandler({
  status,
  resetErrorState,
  errorInfo = { header: '', message: '', action: '' },
}: IErrorHandlerProps) {
  const isUnauthorized = status === 401 || status === 403;
  const errorHeader = isUnauthorized
    ? __("Your plugin isn't authorized", 'leadin')
    : errorInfo.header;
  const errorMessage = isUnauthorized
    ? __('Reauthorize your plugin to access your free HubSpot tools', 'leadin')
    : errorInfo.message;

  return (
    <HubspotWrapper pluginPath={pluginPath}>
      <UIContainer textAlign="center">
        <h4>{errorHeader}</h4>
        <p>
          <b>{errorMessage}</b>
        </p>
        {isUnauthorized ? (
          <UIButton data-test-id="authorize-button" onClick={redirectToPlugin}>
            {__('Go to plugin', 'leadin')}
          </UIButton>
        ) : (
          <UIButton data-test-id="retry-button" onClick={resetErrorState}>
            {errorInfo.action}
          </UIButton>
        )}
      </UIContainer>
    </HubspotWrapper>
  );
}
