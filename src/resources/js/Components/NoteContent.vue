<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { parseNoteContent } from '../noteContent';
import useSnackbar from '../Composables/useSnackbar';
import HighlightedCode from './HighlightedCode.vue';

const props = defineProps<{ content: string; maxLines?: number }>();
const expanded = ref(false);
const { snackbar, showSnackBar } = useSnackbar();
const copyFailed = ref(false);
const blocks = computed(() => parseNoteContent(props.content));
const isTruncated = computed(
  () =>
    !expanded.value &&
    props.maxLines != null &&
    blocks.value.reduce((total, block) => total + block.lines, 0) > props.maxLines
);
const visibleBlocks = computed(() => {
  if (!isTruncated.value) return blocks.value;
  let remaining = props.maxLines;
  const visible = [];
  for (const block of blocks.value) {
    if (remaining <= 0) break;
    // Avoid showing or copying an incomplete code block.
    if (block.lines > remaining) break;
    visible.push(block);
    remaining -= block.lines;
  }
  return visible;
});

watch(
  () => props.content,
  () => {
    expanded.value = false;
    snackbar.value.display = false;
  }
);

async function copyCode(content: string) {
  try {
    await navigator.clipboard.writeText(content);
    copyFailed.value = false;
    showSnackBar('Copied');
  } catch {
    copyFailed.value = true;
    showSnackBar('Copy failed');
  }
}
</script>

<template>
  <template v-for="(block, index) in visibleBlocks" :key="index">
    <p
      v-if="block.type === 'text'"
      class="font-handwritten note-paragraph text-body-large text-pre-wrap"
    >
      {{ block.content }}
    </p>
    <div v-else class="note-code-block">
      <div class="note-code-toolbar">
        <span>{{ block.language || 'Code' }}</span>
        <v-btn
          type="button"
          class="note-code-copy text-capitalize"
          size="small"
          variant="text"
          prepend-icon="mdi-content-copy"
          @click.stop="copyCode(block.content)"
        >
          Copy
        </v-btn>
      </div>
      <HighlightedCode :content="block.content" :language="block.language" />
    </div>
  </template>
  <v-snackbar
    v-model="snackbar.display"
    location="top right"
    :color="copyFailed ? 'error' : 'success'"
    :timeout="3000"
    role="status"
  >
    <v-icon class="me-3" :icon="copyFailed ? 'mdi-alert-circle' : 'mdi-check-circle'" />
    {{ snackbar.message }}
  </v-snackbar>
  <v-btn
    v-if="isTruncated"
    class="text-capitalize ps-0"
    color="primary"
    variant="text"
    density="compact"
    @click.stop="expanded = true"
  >
    Show more
  </v-btn>
</template>

<style scoped>
.note-code-block {
  margin: 0.5rem 0;
  border-radius: 4px;
  background: rgba(var(--v-theme-on-surface), 0.08);
  overflow: hidden;
}
.note-code-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
}
.note-code-block :deep(pre) {
  margin: 0;
  padding: 0.75rem;
  overflow-x: auto;
  font: 0.875rem/1.5 monospace;
  white-space: pre;
}
.note-code-block :deep(.shiki),
.note-code-block :deep(.shiki span) {
  color: var(--shiki-light);
}
.v-theme--dark .note-code-block :deep(.shiki),
.v-theme--dark .note-code-block :deep(.shiki span) {
  color: var(--shiki-dark);
}
</style>
