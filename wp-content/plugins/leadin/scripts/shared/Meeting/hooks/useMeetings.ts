import { useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import {
  CURRENT_USER_CALENDAR_MISSING,
  OTHER_USER_CALENDAR_MISSING,
} from '../constants';
import useMeetingsFetch, { MeetingUser } from './useMeetingsFetch';
import useCurrentUserFetch from './useCurrentUserFetch';
import LoadState from '../../enums/loadState';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import { ProxyMessages } from '../../../iframe/integratedMessages';

function getDefaultMeetingName(
  meeting: any,
  currentUser: any,
  meetingUsers: any
) {
  const [meetingOwnerId] = meeting.meetingsUserIds;
  let result = __('Default', 'leadin');
  if (
    currentUser &&
    meetingOwnerId !== currentUser.id &&
    meetingUsers[meetingOwnerId]
  ) {
    const user = meetingUsers[meetingOwnerId];
    result += ` (${user.userProfile.fullName})`;
  }
  return result;
}

function hasCalendarObject(user: any) {
  return (
    user &&
    user.meetingsUserBlob &&
    user.meetingsUserBlob.calendarSettings &&
    user.meetingsUserBlob.calendarSettings.email
  );
}

export default function useMeetings() {
  const proxy = usePostAsyncBackgroundMessage();
  const {
    meetings,
    meetingUsers,
    error: meetingsError,
    loadMeetingsState,
    reload: reloadMeetings,
  } = useMeetingsFetch();
  const {
    user: currentUser,
    error: userError,
    loadUserState,
    reload: reloadUser,
  } = useCurrentUserFetch();

  const reload = useCallback(() => {
    reloadUser();
    reloadMeetings();
  }, [reloadUser, reloadMeetings]);

  const connectCalendar = () => {
    return proxy({
      key: ProxyMessages.ConnectMeetingsCalendar,
    });
  };

  return {
    mappedMeetings: meetings.map(meet => ({
      label:
        meet.name || getDefaultMeetingName(meet, currentUser, meetingUsers),
      value: meet.link,
    })),
    meetings,
    meetingUsers,
    currentUser,
    error: meetingsError || (userError as any),
    loading:
      loadMeetingsState == LoadState.Loading ||
      loadUserState === LoadState.Loading,
    reload,
    connectCalendar,
  };
}

export function useSelectedMeeting(url: string) {
  const { mappedMeetings: meetings } = useMeetings();
  const option = meetings.find(({ value }) => value === url);

  return option;
}

export function useSelectedMeetingCalendar(url: string) {
  const { meetings, meetingUsers, currentUser } = useMeetings();

  const meeting = meetings.find(meet => meet.link === url);

  const mappedMeetingUsersId: {
    [key: number]: MeetingUser;
  } = meetingUsers.reduce((p, c) => ({ ...p, [c.id]: c }), {});

  if (!meeting) {
    return null;
  } else {
    const { meetingsUserIds } = meeting;
    if (
      currentUser &&
      meetingsUserIds.includes(currentUser.id) &&
      !hasCalendarObject(currentUser)
    ) {
      return CURRENT_USER_CALENDAR_MISSING;
    } else if (
      meetingsUserIds
        .map(id => mappedMeetingUsersId[id])
        .some((user: any) => !hasCalendarObject(user))
    ) {
      return OTHER_USER_CALENDAR_MISSING;
    } else {
      return null;
    }
  }
}
