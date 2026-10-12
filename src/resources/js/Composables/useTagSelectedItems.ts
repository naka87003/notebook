import axios from 'axios';
import { Tag } from '../interfaces';
import { useAsyncState } from '@vueuse/core';

export default function useTagSelectedItems() {
  return useAsyncState(async () => {
    const response = await axios.get<Tag[]>(route('tags.items.select'), {
      params: {},
    });
    return response.data;
  }, []);
}
