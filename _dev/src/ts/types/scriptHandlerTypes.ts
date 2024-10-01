import PageAbstract from '../pages/PageAbstract';

interface RoutesMatching {
  [key: string]: new () => PageAbstract;
}

export type { RoutesMatching };
