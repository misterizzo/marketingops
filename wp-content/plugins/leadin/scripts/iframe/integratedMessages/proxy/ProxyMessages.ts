export const ProxyMessages = {
  FetchForms: 'FETCH_FORMS',
  FetchForm: 'FETCH_FORM',
  CreateFormFromTemplate: 'CREATE_FORM_FROM_TEMPLATE',
  FetchAuth: 'FETCH_AUTH',
  FetchMeetingsAndUsers: 'FETCH_MEETINGS_AND_USERS',
  FetchContactsCreateSinceActivation: 'FETCH_CONTACTS_CREATED_SINCE_ACTIVATION',
  FetchOrCreateMeetingUser: 'FETCH_OR_CREATE_MEETING_USER',
  ConnectMeetingsCalendar: 'CONNECT_MEETINGS_CALENDAR',
  TrackFormPreviewRender: 'TRACK_FORM_PREVIEW_RENDER',
  TrackFormCreatedFromTemplate: 'TRACK_FORM_CREATED_FROM_TEMPLATE',
  TrackFormCreationFailed: 'TRACK_FORM_CREATION_FAILED',
  TrackMeetingPreviewRender: 'TRACK_MEETING_PREVIEW_RENDER',
  TrackSidebarMetaChange: 'TRACK_SIDEBAR_META_CHANGE',
  TrackReviewBannerRender: 'TRACK_REVIEW_BANNER_RENDER',
  TrackReviewBannerInteraction: 'TRACK_REVIEW_BANNER_INTERACTION',
  TrackReviewBannerDismissed: 'TRACK_REVIEW_BANNER_DISMISSED',
  TrackPluginDeactivation: 'TRACK_PLUGIN_DEACTIVATION',
} as const;

export type ProxyMessageType = typeof ProxyMessages[keyof typeof ProxyMessages];
