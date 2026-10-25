import type { InjectionKey } from 'vue';

export const refreshNoteKey: InjectionKey<(id: number) => Promise<void>> =
  Symbol('refreshNote');
