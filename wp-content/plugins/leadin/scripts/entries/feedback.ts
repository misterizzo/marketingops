import $ from 'jquery';
import Raven from '../lib/Raven';
import { domElements } from '../constants/selectors';
import ThickBoxModal from '../feedback/ThickBoxModal';
import { submitFeedbackForm } from '../feedback/feedbackFormApi';
import {
  getOrCreateBackgroundApp,
  initBackgroundApp,
} from '../utils/backgroundAppUtils';
import { ProxyMessages } from '../iframe/integratedMessages';

let embedder: any;

function deactivatePlugin() {
  const href = $(domElements.deactivatePluginButton).attr('href');
  if (href) {
    window.location.href = href;
  }
}

function setLoadingState() {
  $(domElements.deactivateFeedbackSubmit).addClass('loading');
}

function submitAndDeactivate(e: Event) {
  e.preventDefault();
  setLoadingState();
  const feedback = $(domElements.deactivateFeedbackForm)
    .serializeArray()
    .find(field => field.name === 'feedback');

  submitFeedbackForm(domElements.deactivateFeedbackForm)
    .then(() => {
      if (feedback) {
        embedder.postMessage({
          key: ProxyMessages.TrackPluginDeactivation,
          payload: {
            type: feedback.value.trim().replace(/[\s']+/g, '_'),
          },
        });
      }
    })
    .catch((err: Error) => {
      Raven.captureException(err);
    })
    .finally(() => {
      deactivatePlugin();
    });
}

function init() {
  embedder = getOrCreateBackgroundApp();
  // eslint-disable-next-line no-new
  new ThickBoxModal(
    domElements.deactivatePluginButton,
    'leadin-feedback-container',
    'leadin-feedback-window',
    'leadin-feedback-content'
  );

  $(domElements.deactivateFeedbackForm)
    .off('submit')
    .on('submit', submitAndDeactivate);
  $(domElements.deactivateFeedbackSkip)
    .off('click')
    .on('click', deactivatePlugin);
}

initBackgroundApp(init);
