import { ref, computed, provide } from 'vue';
import axios from 'axios';
import type { Note } from '@/interfaces';

export default function useNotes() {
  const notes = ref(new Map<number, Note>());

  const targetNoteId = ref<number | null>(null);

  const targetNote = computed(() => notes.value.get(targetNoteId.value));

  const updatePosts = async (id: number) => {
    const response = await axios.get(route('notes.note', id));
    notes.value.set(id, response.data);
  };

  provide('updatePosts', updatePosts);

  return {
    notes,
    targetNoteId,
    targetNote,
    updatePosts,
  };
}
