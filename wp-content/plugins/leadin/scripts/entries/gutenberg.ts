import registerFormBlock from '../gutenberg/FormBlock/registerFormBlock';
import { registerHubspotSidebar } from '../gutenberg/Sidebar/contentType';
import registerMeetingBlock from '../gutenberg/MeetingsBlock/registerMeetingBlock';
import { initBackgroundApp } from '../utils/backgroundAppUtils';

initBackgroundApp([
  registerFormBlock,
  registerMeetingBlock,
  registerHubspotSidebar,
]);
