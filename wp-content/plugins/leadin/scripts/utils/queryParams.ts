export function addQueryObjectToUrl(
  urlObject: URL,
  queryParams: { [key: string]: any }
) {
  Object.keys(queryParams).forEach(key => {
    urlObject.searchParams.append(key, queryParams[key]);
  });
}

export function removeQueryParamFromLocation(key: string) {
  const location = new URL(window.location.href);
  location.searchParams.delete(key);
  window.history.replaceState(null, '', location.href);
}
