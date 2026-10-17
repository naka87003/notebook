<script setup lang="ts">
import { onWatcherCleanup, ref, watch } from 'vue';
import { bundledLanguages, codeToHtml } from 'shiki';

const props = defineProps<{ content: string; language: string }>();

const highlighted = ref('');

watch(
  () => [props.content, props.language],
  async ([content, language]) => {
    let cancelled = false;
    onWatcherCleanup(() => {
      cancelled = true;
    });
    highlighted.value = '';

    const lang = language.toLowerCase();
    if (!Object.hasOwn(bundledLanguages, lang)) return;

    try {
      const html = await codeToHtml(content, {
        lang,
        themes: { light: 'github-light', dark: 'github-dark' },
        defaultColor: false,
      });
      if (!cancelled) highlighted.value = html;
    } catch {
      // Keep the code readable if a language or theme cannot be loaded.
    }
  },
  { immediate: true }
);
</script>

<template>
  <!-- Shiki escapes the code before generating HTML. -->
  <!-- eslint-disable-next-line vue/no-v-html -->
  <div v-if="highlighted" v-html="highlighted" />
  <pre v-else><code>{{ content }}</code></pre>
</template>
