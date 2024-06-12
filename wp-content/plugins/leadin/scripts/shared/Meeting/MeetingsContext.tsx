import React, { useEffect, useState, useCallback } from 'react';
import useCurrentUserFetch from './hooks/useCurrentUserFetch';
import useMeetingsFetch from './hooks/useMeetingsFetch';
import LoadState from '../enums/loadState';

interface IMeetingsContextWrapperState {
  loading: boolean;
  error: any;
  meetings: any[];
  currentUser: any;
  meetingUsers: any;
  selectedMeeting: string;
}

interface IMeetingsContext extends IMeetingsContextWrapperState {
  reload: Function;
}

interface IMeetingsContextWrapperProps {
  url: string;
}

export const MeetingsContext = React.createContext<IMeetingsContext>({
  loading: true,
  error: null,
  meetings: [],
  currentUser: null,
  meetingUsers: {},
  selectedMeeting: '',
  reload: () => {},
});

export default function MeetingsContextWrapper({
  url,
  children,
}: React.PropsWithChildren<IMeetingsContextWrapperProps>) {
  const [state, setState] = useState<IMeetingsContextWrapperState>({
    loading: true,
    error: null,
    meetings: [],
    currentUser: null,
    meetingUsers: {},
    selectedMeeting: url,
  });

  const {
    meetings,
    meetingUsers,
    loadMeetingsState,
    error: errorMeeting,
    reload: reloadMeetings,
  } = useMeetingsFetch();

  const {
    user: currentUser,
    loadUserState,
    error: errorUser,
    reload: reloadUser,
  } = useCurrentUserFetch();

  const reload = useCallback(() => {
    reloadUser();
    reloadMeetings();
  }, [reloadUser, reloadMeetings]);

  useEffect(() => {
    if (
      !state.loading &&
      !state.error &&
      state.currentUser &&
      state.meetings.length === 0
    ) {
      reloadMeetings();
    }
  }, [state, reloadMeetings]);

  useEffect(() => {
    setState(previous => ({
      ...previous,
      loading:
        loadUserState === LoadState.Loading ||
        loadMeetingsState === LoadState.Loading,
      currentUser,
      meetings,
      meetingUsers: meetingUsers.reduce((p, c) => ({ ...p, [c.id]: c }), {}),
      error: errorMeeting || errorUser,
      selectedMeeting: url,
    }));
  }, [
    loadUserState,
    loadMeetingsState,
    currentUser,
    meetings,
    meetingUsers,
    errorMeeting,
    errorUser,
    url,
    setState,
  ]);

  return (
    <MeetingsContext.Provider value={{ ...state, reload }}>
      {children}
    </MeetingsContext.Provider>
  );
}
