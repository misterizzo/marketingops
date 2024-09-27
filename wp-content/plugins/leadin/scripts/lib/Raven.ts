import Raven from 'raven-js';
import {
  hubspotBaseUrl,
  phpVersion,
  wpVersion,
  leadinPluginVersion,
  portalId,
  plugins,
} from '../constants/leadinConfig';

export function configureRaven() {
  if (hubspotBaseUrl.indexOf('app.hubspot.com') === -1) {
    return;
  }

  Raven.config(
    'https://e9b8f382cdd130c0d415cd977d2be56f@exceptions.hubspot.com/1',
    {
      instrument: {
        tryCatch: false,
      },
      shouldSendCallback(data) {
        return (
          !!data && !!data.culprit && /plugins\/leadin\//.test(data.culprit)
        );
      },
      release: leadinPluginVersion,
    }
  ).install();

  Raven.setTagsContext({
    v: leadinPluginVersion,
    php: phpVersion,
    wordpress: wpVersion,
  });

  Raven.setExtraContext({
    hub: portalId,
    plugins: Object.keys(plugins)
      .map(name => `${name}#${plugins[name]}`)
      .join(','),
  });
}

export default Raven;
