import type { User } from '@/interfaces';
import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy/src/js';

type RouteHelper = typeof ziggyRoute;

declare global {
  const route: RouteHelper;
}

declare module '@inertiajs/core' {
  interface PageProps {
    auth: {
      user: User | null;
    };
    unreadNotificationCount?: number;
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    route: RouteHelper;
  }
}

export {};
