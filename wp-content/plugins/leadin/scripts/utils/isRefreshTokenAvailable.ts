import { refreshToken } from '../constants/leadinConfig';

export function isRefreshTokenAvailable() {
  return !!(refreshToken && refreshToken.trim());
}
