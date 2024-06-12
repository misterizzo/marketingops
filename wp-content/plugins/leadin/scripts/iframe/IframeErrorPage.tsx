import React from 'react';
import { __ } from '@wordpress/i18n';
import { styled } from '@linaria/react';

const IframeErrorContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: 120px;
  font-family: 'Lexend Deca', Helvetica, Arial, sans-serif;
  font-weight: 400;
  font-size: 14px;
  font-size: 0.875rem;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-smoothing: antialiased;
  line-height: 1.5rem;
`;

const ErrorHeader = styled.h1`
  text-shadow: 0 0 1px transparent;
  margin-bottom: 1.25rem;
  color: #33475b;
  font-size: 1.25rem;
`;

export const IframeErrorPage = () => (
  <IframeErrorContainer>
    <img
      alt="Cannot find page"
      width="175"
      src="//static.hsappstatic.net/ui-images/static-1.14/optimized/errors/map.svg"
    />
    <ErrorHeader>
      {__(
        'The HubSpot for WordPress plugin is not able to load pages',
        'leadin'
      )}
    </ErrorHeader>
    <p>
      {__(
        'Try disabling your browser extensions and ad blockers, then refresh the page',
        'leadin'
      )}
    </p>
    <p>
      {__(
        'Or open the HubSpot for WordPress plugin in a different browser',
        'leadin'
      )}
    </p>
  </IframeErrorContainer>
);
