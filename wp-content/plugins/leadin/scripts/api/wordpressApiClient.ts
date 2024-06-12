import $ from 'jquery';

import Raven from '../lib/Raven';
import { restNonce, restUrl } from '../constants/leadinConfig';
import { addQueryObjectToUrl } from '../utils/queryParams';

function makeRequest(
  method: string,
  path: string,
  data: any = {},
  queryParams = {}
): Promise<any> {
  // eslint-disable-next-line compat/compat
  const restApiUrl = new URL(`${restUrl}leadin/v1${path}`);
  addQueryObjectToUrl(restApiUrl, queryParams);

  return new Promise((resolve, reject) => {
    const payload: { [key: string]: any } = {
      url: restApiUrl.toString(),
      method,
      contentType: 'application/json',
      beforeSend: (xhr: any) => xhr.setRequestHeader('X-WP-Nonce', restNonce),
      success: resolve,
      error: (response: any) => {
        Raven.captureMessage(
          `HTTP Request to ${restApiUrl} failed with error ${response.status}: ${response.responseText}`,
          {
            fingerprint: [
              '{{ default }}',
              path,
              response.status,
              response.responseText,
            ],
          }
        );
        reject(response);
      },
    };

    if (method !== 'get') {
      payload.data = JSON.stringify(data);
    }

    $.ajax(payload);
  });
}

export function healthcheckRestApi() {
  return makeRequest('get', '/healthcheck');
}

export function disableInternalTracking(value: boolean) {
  return makeRequest('put', '/internal-tracking', value ? '1' : '0');
}

export function fetchDisableInternalTracking() {
  return makeRequest('get', '/internal-tracking').then(message => ({
    message,
  }));
}

export function updateHublet(hublet: string) {
  return makeRequest('put', '/hublet', { hublet });
}

export function skipReview() {
  return makeRequest('post', '/skip-review');
}

export function trackConsent(canTrack: boolean) {
  return makeRequest('post', '/track-consent', { canTrack }).then(message => ({
    message,
  }));
}

export function setBusinessUnitId(businessUnitId: number) {
  return makeRequest('put', '/business-unit', { businessUnitId });
}

export function getBusinessUnitId() {
  return makeRequest('get', '/business-unit');
}
