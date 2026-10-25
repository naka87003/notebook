<script setup lang="ts">
import type { Note } from '@/Types/interfaces';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { simplifyDateTime, relativeDateTime, imageUrl } from '@/Utils/common';
import NoteContent from './NoteContent.vue';

const props = defineProps<{
  note: Note;
  commentLinkDisabled?: boolean;
}>();

defineEmits<{
  showEnlargedImage: [src: string];
  showLikedUserList: [];
  showComments: [];
}>();

const previewImagePath = computed(() => {
  return imageUrl(props.note.image_path);
});

const showTaggedNotes = () => {
  router.get(route('dashboard'), {
    tag: props.note.tag.id,
    status: props.note.status_id,
  });
};
</script>

<template>
  <v-card
    :color="note.category.vuetify_theme_color_name"
    variant="tonal"
    class="mx-auto"
    :prepend-icon="note.category.mdi_name"
    :title="note.title"
    density="compact"
    rounded="0"
  >
    <template #title>
      <span class="font-handwritten text-body-large">{{ note.title }}</span>
    </template>
    <template #append>
      <p class="text-body-small ma-0">
        {{ relativeDateTime(note.updated_at) }}
      </p>
    </template>
    <v-divider class="border-opacity-25 mx-1" />
    <v-card-text class="text-title-large py-2">
      <v-alert v-if="note.category.id === 3" type="info" variant="tonal" class="mb-3">
        <p class="text-body-medium ma-0">from {{ simplifyDateTime(note.starts_at) }}</p>
        <p class="text-body-medium ma-0">to {{ simplifyDateTime(note.ends_at) }}</p>
      </v-alert>
      <NoteContent :content="note.content ?? ''" :max-lines="5" />
      <v-img
        v-if="previewImagePath"
        :src="previewImagePath"
        width="300"
        class="mt-3 cursor-pointer"
        style="z-index: 1"
        lazy-src="/lazy-src.gif"
        @click="$emit('showEnlargedImage', previewImagePath)"
      >
        <template #placeholder>
          <div class="d-flex align-center justify-center fill-height">
            <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
          </div>
        </template>
      </v-img>
    </v-card-text>
    <v-card-actions class="flex-wrap justify-space-between gc-2 gr-0">
      <v-btn v-if="note.tag" @click="showTaggedNotes">
        <template #prepend>
          <v-icon :color="note.tag?.hex_color">mdi-tag</v-icon>
        </template>
        <span class="text-body-small">{{ note.tag?.name }}</span>
        <v-tooltip activator="parent" location="bottom" text="Filter by tag" />
      </v-btn>
      <div class="d-flex align-center flex-grow-1">
        <v-btn
          v-if="note.status.name === 'archived'"
          size="small"
          icon="mdi-archive-outline"
          readonly
        />
        <v-btn v-if="!note.public" size="small" icon="mdi-lock-outline" readonly />
        <v-btn
          v-if="note.public && note.comments_count"
          prepend-icon="mdi-comment-outline"
          :readonly="Boolean(commentLinkDisabled)"
          @click="$emit('showComments')"
        >
          {{ note.comments_count || '' }}
          <v-tooltip activator="parent" location="bottom" text="Show comments" />
        </v-btn>
        <v-btn
          v-if="note.public && note.likes_count"
          prepend-icon="mdi-heart"
          @click="$emit('showLikedUserList')"
        >
          {{ note.likes_count }}
          <v-tooltip activator="parent" location="bottom" text="Show liked users" />
        </v-btn>
        <v-spacer />
        <slot name="actions" />
      </div>
    </v-card-actions>
  </v-card>
</template>
