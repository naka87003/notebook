export type InfiniteScrollLoadOptions = {
  done: (status: 'ok' | 'empty' | 'loading' | 'error') => void;
};
