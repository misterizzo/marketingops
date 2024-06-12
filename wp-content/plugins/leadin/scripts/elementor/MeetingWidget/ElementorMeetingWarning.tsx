import React, { Fragment } from 'react';
import { CURRENT_USER_CALENDAR_MISSING } from '../../shared/Meeting/constants';
import ElementorButton from '../Common/ElementorButton';
import ElementorBanner from '../Common/ElementorBanner';
import { styled } from '@linaria/react';
import { __ } from '@wordpress/i18n';

const Container = styled.div`
  padding-bottom: 8px;
`;

interface IMeetingWarningPros {
  onConnectCalendar: React.MouseEventHandler<HTMLButtonElement>;
  status: string;
}

export default function MeetingWarning({
  onConnectCalendar,
  status,
}: IMeetingWarningPros) {
  const isMeetingOwner = status === CURRENT_USER_CALENDAR_MISSING;
  const titleText = isMeetingOwner
    ? __('Your calendar is not connected', 'leadin')
    : __('Calendar is not connected', 'leadin');
  const titleMessage = isMeetingOwner
    ? __(
        'Please connect your calendar to activate your scheduling pages',
        'leadin'
      )
    : __(
        'Make sure that everybody in this meeting has connected their calendar from the Meetings page in HubSpot',
        'leadin'
      );
  return (
    <Fragment>
      <Container>
        <ElementorBanner type="warning">
          <b>{titleText}</b>
          <br />
          {titleMessage}
        </ElementorBanner>
      </Container>
      {isMeetingOwner && (
        <ElementorButton
          id="meetings-connect-calendar"
          onClick={onConnectCalendar}
        >
          {__('Connect calendar', 'leadin')}
        </ElementorButton>
      )}
    </Fragment>
  );
}
