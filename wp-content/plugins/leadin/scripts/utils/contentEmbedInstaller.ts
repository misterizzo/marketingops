type ContentEmbedInfoResponse = {
  success: boolean;
  data?: {
    // Empty if user doesn't have permissions or plugin already activated
    activateAjaxUrl?: string;
    message: string;
  };
};

export function startInstall(nonce: string) {
  const formData = new FormData();
  const ajaxUrl = (window as any).ajaxurl;
  formData.append('_wpnonce', nonce);
  formData.append('action', 'content_embed_install');
  return fetch(ajaxUrl, {
    method: 'POST',
    body: formData,
    keepalive: true,
  }).then<ContentEmbedInfoResponse>(res => res.json());
}

export function startActivation(requestUrl: string) {
  return fetch(requestUrl, {
    method: 'POST',
    keepalive: true,
  }).then<ContentEmbedInfoResponse>(res => res.json());
}
