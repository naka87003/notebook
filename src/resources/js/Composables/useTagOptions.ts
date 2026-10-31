import axios from 'axios';
import type { Tag } from '@/Types/interfaces';
import { useAsyncState } from '@vueuse/core';

export default function useTagOptions() {
  return useAsyncState(async () => {
    const response = await axios.get<Tag[]>(route('tags.items.select'), {
      params: {},
    });
    return response.data;
  }, []);
}
