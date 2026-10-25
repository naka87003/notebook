<script setup lang="ts">
import type { Note } from '@/Types/interfaces';
import { inject, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { simplifyDateTime, relativeDateTime, imageUrl } from '@/Utils/common';
import NoteContent from './NoteContent.vue';

const page = usePage();
const props = defineProps<{
  note: Note;
  commentLinkDisabled?: boolean;
}>();

defineEmits<{
  showComments: [];
}>();

const showEnlargedImage = inject<(src: string) => void>('showEnlargedImage');

const updatePosts = inject<(id: number) => Promise<void>>('updatePosts');

const likeCount = computed((): number => props.note.likes_count);

const isLiked = computed((): boolean =>
  props.note.likes.some((like) => like.user_id === page.props.auth.user.id)
);

const previewImagePath = computed(() => {
  return imageUrl(props.note.image_path);
});

const like = async () => {
  if (isLiked.value === false) {
    await axios.post(route('likes.like', props.note.id));
    updatePosts(props.note.id);
  } else {
    await axios.delete(route('likes.unlike', props.note.id));
    updatePosts(props.note.id);
  }
};

const showSelectedUserPosts = (userId: number) => {
  router.get(route('timeline'), {
    user: userId,
  });
};
</script>

<template>
  <v-card
    :color="note.category.vuetify_theme_color_name"
    variant="tonal"
    class="mx-auto"
    density="compact"
    :prepend-icon="note.category.mdi_name"
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
        @click="showEnlargedImage(previewImagePath)"
      >
        <template #placeholder>
          <div class="d-flex align-center justify-center fill-height">
            <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
          </div>
        </template>
      </v-img>
    </v-card-text>
    <v-card-actions class="flex-wrap">
      <v-list-item prepend-gap="10" @click="showSelectedUserPosts(note.user.id)">
        <template #prepend>
          <v-avatar color="grey-darken-3" size="x-small" style="z-index: 1">
            <v-img v-if="note.user.image_path" :src="imageUrl(note.user.image_path)" />
            <v-icon v-else icon="mdi-account" />
          </v-avatar>
        </template>
        <v-list-item-title>{{ note.user.name }}</v-list-item-title>
      </v-list-item>
      <div class="d-flex align-center ms-auto">
        <div v-if="note.tag" class="d-flex align-center ga-2 pl-2 pr-3">
          <v-icon :color="note.tag?.hex_color" size="small">mdi-tag</v-icon>
          <span class="text-body-small">{{ note.tag?.name }}</span>
        </div>
        <v-btn
          prepend-icon="mdi-comment-outline"
          :readonly="Boolean(commentLinkDisabled)"
          @click="$emit('showComments')"
        >
          {{ note.comments_count || '' }}
          <v-tooltip activator="parent" location="bottom" text="Show comments" />
        </v-btn>
        <v-btn
          :prepend-icon="isLiked ? 'mdi-heart' : 'mdi-heart-outline'"
          :class="{ 'text-pink': isLiked }"
          @click="like"
        >
          {{ likeCount }}
          <v-tooltip activator="parent" location="bottom" :text="isLiked ? 'Unlike' : 'Like'" />
        </v-btn>
      </div>
    </v-card-actions>
  </v-card>
</template>
