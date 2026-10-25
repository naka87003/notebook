import { ref, computed, provide } from 'vue';
import axios from 'axios';
import { refreshNoteKey } from '@/Keys/notes';
import type { Note } from '@/Types/interfaces';

export default function useNotes() {
  const notes = ref(new Map<number, Note>());

  const targetNoteId = ref<number | null>(null);

  const targetNote = computed(() =>
    targetNoteId.value === null ? undefined : notes.value.get(targetNoteId.value)
  );

  const refreshNote = async (id: number) => {
    const response = await axios.get(route('notes.note', id));
    notes.value.set(id, response.data);
  };

  provide(refreshNoteKey, refreshNote);

  return {
    notes,
    targetNoteId,
    targetNote,
    refreshNote,
  };
}
