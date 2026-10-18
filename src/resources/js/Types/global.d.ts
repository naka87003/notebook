import type { User } from '@/Types/interfaces';
import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy/src/js';

type RouteHelper = typeof ziggyRoute;

declare global {
  const route: RouteHelper;
}

declare module '@inertiajs/core' {
  interface InertiaConfig {
    sharedPageProps: {
      auth: {
        user: User | null;
      };
      noteContentMaxLength: number;
    };
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    route: RouteHelper;
  }
}

export {};
