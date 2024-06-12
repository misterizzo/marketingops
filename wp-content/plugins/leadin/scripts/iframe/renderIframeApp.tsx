import React, { Fragment } from 'react';
import ReactDOM from 'react-dom';
import { domElements } from '../constants/selectors';
import useAppEmbedder from './useAppEmbedder';
import { App } from './constants';
import { IframeErrorPage } from './IframeErrorPage';

interface PortalProps extends React.PropsWithChildren {
  app: App;
  createRoute: boolean;
}

const IntegratedIframePortal = (props: PortalProps) => {
  const container = document.getElementById(domElements.leadinIframeContainer);
  const iframeNotRendered = useAppEmbedder(
    props.app,
    props.createRoute,
    container
  );

  if (container && !iframeNotRendered) {
    return ReactDOM.createPortal(props.children, container);
  }

  return (
    <Fragment>
      {(!container || iframeNotRendered) && <IframeErrorPage />}
    </Fragment>
  );
};

const renderIframeApp = () => {
  const iframeFallbackContainer = document.getElementById(
    domElements.leadinIframeContainer
  );

  let app: App;
  const queryParams = new URLSearchParams(location.search);
  const page = queryParams.get('page');
  const createRoute = queryParams.get('leadin_route[0]') === 'create';

  switch (page) {
    case 'leadin_forms':
      app = App.Forms;
      break;
    case 'leadin_chatflows':
      app = App.LiveChat;
      break;
    case 'leadin_settings':
      app = App.PluginSettings;
      break;
    case 'leadin_user_guide':
    default:
      app = App.Plugin;
      break;
  }

  ReactDOM.render(
    <IntegratedIframePortal app={app} createRoute={createRoute} />,
    iframeFallbackContainer
  );
};

export default renderIframeApp;
