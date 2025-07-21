import { ref, computed, type Ref } from 'vue';
import { splitByNewline } from '@/common';
import type { Note } from '@/interfaces';

export default function useParagraphs(note: Ref<Note>) {
  const truncate = ref(true);
  const arrCommentLines = computed(() => splitByNewline(note.value.content ?? ''));

  const isTruncated = computed(() => truncate.value && arrCommentLines.value.length > 5);

  const paragraphs = computed(() => {
    let lines = arrCommentLines.value;
    if (truncate.value && lines.length > 5) {
      lines = lines.slice(0, 5);
      lines[lines.length - 1] += '...';
    }
    return lines;
  });

  return {
    truncate,
    arrCommentLines,
    isTruncated,
    paragraphs,
  };
}
