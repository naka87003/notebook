<script setup lang="ts">
import type { Note } from '@/interfaces';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { simplifyDateTime, splitByNewline, relativeDateTime } from '@/common';

const props = defineProps<{
  note: Note
  commentLinkDisabled?: boolean;
}>();

defineEmits<{
  showEnlargedImage: [src: string];
  showLikedUserList: [];
  showComments: [];
}>();

const previewImagePath = computed(() => {
  return props.note.image_path ? '/storage/' + props.note.image_path : null;
});

const showTaggedNotes = () => {
  router.get(route('dashboard'), {
    tag: props.note.tag.id,
    status: props.note.status_id
  });
};
</script>

<template>
  <v-card :color="note.category.vuetify_theme_color_name" variant="tonal" class="mx-auto"
    :prepend-icon="note.category.mdi_name" :title="note.title" rounded="0">
    <template v-slot:prepend>
      <v-icon size="large"></v-icon>
    </template>
    <template v-slot:append>
      <p class="text-caption">
        {{ relativeDateTime(note.updated_at) }}
      </p>
    </template>
    <v-divider class="border-opacity-25 mx-1" />
    <v-card-text class="text-h6 py-2">
      <v-alert v-if="note.category.id === 3" type="info" variant="tonal" class="mb-3">
        <p class="text-body-2">from {{ simplifyDateTime(note.starts_at) }}</p>
        <p class="text-body-2">to {{ simplifyDateTime(note.ends_at) }}</p>
      </v-alert>
      <p v-for="paragraph in splitByNewline(note.content ?? '')" class="note-paragraph text-body-1">{{ paragraph }}</p>
      <v-img v-if="previewImagePath" :src="previewImagePath" width="300" class="mt-3 cursor-pointer" style="z-index: 1;"
        lazy-src="/lazy-src.gif" @click="$emit('showEnlargedImage', previewImagePath)">
        <template v-slot:placeholder>
          <div class="d-flex align-center justify-center fill-height">
            <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
          </div>
        </template>
      </v-img>
    </v-card-text>
    <v-card-actions v-if="note.tag" class="hidden-sm-and-up">
      <v-btn @click="showTaggedNotes">
        <template #prepend>
          <v-icon :color="note.tag?.hex_color">mdi-tag</v-icon>
        </template>
        <span class="text-caption">{{ note.tag?.name }}</span>
      </v-btn>
    </v-card-actions>
    <v-card-actions>
      <template v-if="note.tag">
        <v-btn class="hidden-xs" @click="showTaggedNotes">
          <template #prepend>
            <v-icon :color="note.tag?.hex_color">mdi-tag</v-icon>
          </template>
          <span class="text-caption">{{ note.tag?.name }}</span>
          <v-tooltip activator="parent" location="bottom" text="Filter by tag" />
        </v-btn>
      </template>
      <v-btn v-if="note.status.name === 'archived'" size="small" icon="mdi-archive-outline" readonly />
      <v-btn v-if="!note.public" size="small" icon="mdi-lock-outline" readonly />
      <v-btn v-if="note.public && note.comments_count" prepend-icon="mdi-comment-outline"
        :readonly="Boolean(commentLinkDisabled)" @click="$emit('showComments')">
        {{ note.comments_count || '' }}
        <v-tooltip activator="parent" location="bottom" text="Show comments" />
      </v-btn>
      <v-btn v-if="note.public && note.likes_count" prepend-icon="mdi-heart" @click="$emit('showLikedUserList')">
        {{ note.likes_count }}
        <v-tooltip activator="parent" location="bottom" text="Show liked users" />
      </v-btn>
      <v-spacer />
      <slot name="actions" />
    </v-card-actions>
  </v-card>
</template>

<style scoped>
.note-paragraph {
  background-image: linear-gradient(180deg, rgba(204, 204, 204, 0) 0%, rgba(204, 204, 204, 0) 98.5%, rgba(100, 100, 100, 100) 100%);
  background-repeat: repeat-y;
  background-size: 100% 1.5em;
  line-height: 1.5;
}
</style>