import React, { Fragment, useEffect } from 'react';
import LoadingBlock from '../Common/LoadingBlock';
import MeetingSelector from './MeetingSelector';
import MeetingWarning from './MeetingWarning';
import useMeetings, {
  useSelectedMeeting,
  useSelectedMeetingCalendar,
} from './hooks/useMeetings';
import HubspotWrapper from '../Common/HubspotWrapper';
import ErrorHandler from '../Common/ErrorHandler';
import { pluginPath } from '../../constants/leadinConfig';
import { __ } from '@wordpress/i18n';
import Raven from 'raven-js';

interface IMeetingControllerProps {
  url: string;
  handleChange: Function;
}

export default function MeetingController({
  handleChange,
  url,
}: IMeetingControllerProps) {
  const {
    mappedMeetings: meetings,
    loading,
    error,
    reload,
    connectCalendar,
  } = useMeetings();
  const selectedMeetingOption = useSelectedMeeting(url);
  const selectedMeetingCalendar = useSelectedMeetingCalendar(url);

  useEffect(() => {
    if (!url && meetings.length > 0) {
      handleChange(meetings[0].value);
    }
  }, [meetings, url, handleChange]);

  const handleLocalChange = (option: { value: string }) => {
    handleChange(option.value);
  };

  const handleConnectCalendar = () => {
    return connectCalendar()
      .then(() => {
        reload();
      })
      .catch(error => {
        Raven.captureMessage('Unable to connect calendar', {
          extra: { error },
        });
      });
  };

  return (
    <Fragment>
      {loading ? (
        <LoadingBlock />
      ) : error ? (
        <ErrorHandler
          status={(error && error.status) || error}
          resetErrorState={() => reload()}
          errorInfo={{
            header: __(
              'There was a problem retrieving your meetings',
              'leadin'
            ),
            message: __(
              'Please refresh your meetings or try again in a few minutes',
              'leadin'
            ),
            action: __('Refresh meetings', 'leadin'),
          }}
        />
      ) : (
        <HubspotWrapper padding="90px 32px 24px" pluginPath={pluginPath}>
          {selectedMeetingCalendar && (
            <MeetingWarning
              status={selectedMeetingCalendar}
              onConnectCalendar={handleConnectCalendar}
            />
          )}
          {meetings.length > 1 && (
            <MeetingSelector
              onChange={handleLocalChange}
              options={meetings}
              value={selectedMeetingOption}
            />
          )}
        </HubspotWrapper>
      )}
    </Fragment>
  );
}
