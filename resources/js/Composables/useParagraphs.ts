import { ref, computed, type ComputedRef } from 'vue';
import { splitByNewline } from '@/common';

export default function useParagraphs(content: ComputedRef<string>, rowsCount: number) {
  const truncate = ref(true);
  const arrCommentLines = computed(() => splitByNewline(content.value));

  const isTruncated = computed(() => truncate.value && arrCommentLines.value.length > rowsCount);

  const paragraphs = computed(() => {
    let lines = arrCommentLines.value;
    if (truncate.value && lines.length > rowsCount) {
      lines = lines.slice(0, rowsCount);
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
