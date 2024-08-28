<script setup lang="ts">
import type { Note } from '@/interfaces';
import { computed, ref } from 'vue';
import { simplifyDateTime, splitByNewline } from '@/common';

const props = defineProps<{ targetNote: Note }>();

const emit = defineEmits<{
  close: []
}>();

const dialog = ref({
  enlargedImage: false
})

const previewImagePath = computed(() => {
  return props.targetNote.image_path ? 'storage/' + props.targetNote.image_path : null;
});
</script>

<template>
  <v-card rounded="0">
    <v-toolbar density="comfortable" color="transparent" :title="targetNote.title">
      <template v-slot:prepend>
        <v-icon :icon="targetNote.category.mdi_name" size="large" class="ms-3"></v-icon>
      </template>
      <template v-slot:append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider class="border-opacity-25 mx-1" />
    <v-card-text class="text-h6 py-2">
      <v-alert v-if="targetNote.category.id === 3" type="info" variant="tonal" class="mb-3">
        <p class="text-body-2">from {{ simplifyDateTime(targetNote.starts_at) }}</p>
        <p class="text-body-2">to {{ simplifyDateTime(targetNote.ends_at) }}</p>
      </v-alert>
      <p v-for="paragraph in splitByNewline(targetNote.content ?? '')" class="note-paragraph text-body-1">{{ paragraph
        }}
      </p>
      <v-img v-if="previewImagePath" :src="previewImagePath" width="300" class="mt-3 cursor-pointer"
        @click="dialog.enlargedImage = true" />
    </v-card-text>
    <v-card-actions v-if="targetNote.tag" class="hidden-sm-and-up mx-2">
      <v-btn>
        <template #prepend>
          <v-icon :color="targetNote.tag?.hex_color">mdi-tag</v-icon>
        </template>
        <span class="text-caption">{{ targetNote.tag?.name }}</span>
      </v-btn>
      <v-spacer />
    </v-card-actions>
    <v-card-actions class="mx-2">
      <template v-if="targetNote.tag">
        <v-btn class="hidden-xs" readonly>
          <template #prepend>
            <v-icon :color="targetNote.tag?.hex_color">mdi-tag</v-icon>
          </template>
          <span class="text-caption">{{ targetNote.tag?.name }}</span>
        </v-btn>
        <v-icon v-if="targetNote.status.name === 'archived'" size="small" class="me-5" icon="mdi-archive-outline" />
        <v-icon v-if="targetNote.public === false" size="small" class="me-5" icon="mdi-lock-outline"></v-icon>
      </template>
      <v-spacer />
      <slot name="actions" :targetNote />
    </v-card-actions>
  </v-card>
  <v-dialog v-model="dialog.enlargedImage" close-on-content-click maxWidth="1000px">
    <v-img :src="previewImagePath" height="90vh" />
  </v-dialog>
</template>

<style scoped>
.note-paragraph {
  background-image: linear-gradient(180deg, rgba(204, 204, 204, 0) 0%, rgba(204, 204, 204, 0) 98.5%, rgba(100, 100, 100, 100) 100%);
  background-repeat: repeat-y;
  background-size: 100% 1.5em;
  line-height: 1.5;
}
</style>