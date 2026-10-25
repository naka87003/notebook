import type { InjectionKey } from 'vue';

export const updatePostsKey: InjectionKey<(id: number) => Promise<void>> =
  Symbol('updatePosts');
