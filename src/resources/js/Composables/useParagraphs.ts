import { ref, computed, type ComputedRef } from 'vue';
import { splitByNewline } from '@/Utils/common';

export default function useParagraphs(content: ComputedRef<string>, rowsCount: number) {
  const truncate = ref(true);
  const contentLines = computed(() => splitByNewline(content.value));

  const isTruncated = computed(() => truncate.value && contentLines.value.length > rowsCount);

  const paragraphs = computed(() => {
    let lines = contentLines.value;
    if (truncate.value && lines.length > rowsCount) {
      lines = lines.slice(0, rowsCount);
      lines[lines.length - 1] += '...';
    }
    return lines;
  });

  return {
    truncate,
    isTruncated,
    paragraphs,
  };
}
