const LoadState = {
  NotLoaded: 'NotLoaded',
  Loading: 'Loading',
  Loaded: 'Loaded',
  Idle: 'Idle',
  Failed: 'Failed',
} as const;

export type LoadStateType = typeof LoadState[keyof typeof LoadState];

export default LoadState;
