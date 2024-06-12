import { useEffect, useState } from 'react';
import { usePostAsyncBackgroundMessage } from '../../../iframe/useBackgroundApp';
import LoadState, { LoadStateType } from '../../enums/loadState';
import { ProxyMessages } from '../../../iframe/integratedMessages';

export interface Meeting {
  meetingsUserIds: number[];
  name: string;
  link: string;
}

export interface MeetingUser {
  id: string;
}

let meetings: Meeting[] = [];
let meetingUsers: MeetingUser[] = [];

export default function useMeetingsFetch() {
  const proxy = usePostAsyncBackgroundMessage();
  const [loadState, setLoadState] = useState<LoadStateType>(
    LoadState.NotLoaded
  );

  const [error, setError] = useState(null);

  const reload = () => {
    meetings = [];
    setError(null);
    setLoadState(LoadState.NotLoaded);
  };

  useEffect(() => {
    if (loadState === LoadState.NotLoaded && meetings.length === 0) {
      setLoadState(LoadState.Loading);
      proxy({
        key: ProxyMessages.FetchMeetingsAndUsers,
      })
        .then(data => {
          setLoadState(LoadState.Loaded);
          meetings = data && data.meetingLinks;
          meetingUsers = data && data.meetingUsers;
        })
        .catch(e => {
          setError(e);
          setLoadState(LoadState.Failed);
        });
    }
  }, [loadState]);

  return {
    meetings,
    meetingUsers,
    loadMeetingsState: loadState,
    error,
    reload,
  };
}
