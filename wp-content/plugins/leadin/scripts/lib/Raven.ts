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
  if (hubspotBaseUrl.indexOf('local') !== -1) {
    return;
  }
  const domain = hubspotBaseUrl.replace(/https?:\/\/app/, '');
  Raven.config(
    `https://a9f08e536ef66abb0bf90becc905b09e@exceptions${domain}/v2/1`,
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
