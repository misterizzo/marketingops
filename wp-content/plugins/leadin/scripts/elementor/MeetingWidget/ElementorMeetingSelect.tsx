import React, { Fragment, useState } from 'react';
import ElementorBanner from '../Common/ElementorBanner';
import UISpinner from '../../shared/UIComponents/UISpinner';
import ElementorMeetingWarning from './ElementorMeetingWarning';
import useMeetings, {
  useSelectedMeetingCalendar,
} from '../../shared/Meeting/hooks/useMeetings';
import { __ } from '@wordpress/i18n';
import Raven from 'raven-js';
import {
  BackgroudAppContext,
  useBackgroundAppContext,
} from '../../iframe/useBackgroundApp';
import { refreshToken } from '../../constants/leadinConfig';
import { getOrCreateBackgroundApp } from '../../utils/backgroundAppUtils';

interface IElementorMeetingSelectProps {
  url: string;
  setAttributes: Function;
}

function ElementorMeetingSelect({
  url,
  setAttributes,
}: IElementorMeetingSelectProps) {
  const {
    mappedMeetings: meetings,
    loading,
    error,
    reload,
    connectCalendar,
  } = useMeetings();
  const selectedMeetingCalendar = useSelectedMeetingCalendar(url);
  const [localUrl, setLocalUrl] = useState(url);

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
        <div>
          <UISpinner />
        </div>
      ) : error ? (
        <ElementorBanner type="danger">
          {__(
            'Please refresh your meetings or try again in a few minutes',
            'leadin'
          )}
        </ElementorBanner>
      ) : (
        <Fragment>
          {selectedMeetingCalendar && (
            <ElementorMeetingWarning
              status={selectedMeetingCalendar}
              onConnectCalendar={connectCalendar}
            />
          )}
          {meetings.length > 1 && (
            <select
              value={localUrl}
              onChange={event => {
                const newUrl = event.target.value;
                setLocalUrl(newUrl);
                setAttributes({
                  url: newUrl,
                });
              }}
            >
              <option value="" disabled={true} selected={true}>
                {__('Select a meeting', 'leadin')}
              </option>
              {meetings.map(item => (
                <option key={item.value} value={item.value}>
                  {item.label}
                </option>
              ))}
            </select>
          )}
        </Fragment>
      )}
    </Fragment>
  );
}

function ElementorMeetingSelectWrapper(props: IElementorMeetingSelectProps) {
  const isBackgroundAppReady = useBackgroundAppContext();

  return (
    <Fragment>
      {!isBackgroundAppReady ? (
        <div>
          <UISpinner />
        </div>
      ) : (
        <ElementorMeetingSelect {...props} />
      )}
    </Fragment>
  );
}

export default function ElementorMeetingsSelectContainer(
  props: IElementorMeetingSelectProps
) {
  return (
    <BackgroudAppContext.Provider
      value={refreshToken && getOrCreateBackgroundApp(refreshToken)}
    >
      <ElementorMeetingSelectWrapper {...props} />
    </BackgroudAppContext.Provider>
  );
}
