<script setup lang="ts">
import type { Note } from '@/interfaces';
import { inject, computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { simplifyDateTime, splitByNewline, relativeDateTime } from '@/common';

const props = defineProps<{
  note: Note;
  commentLinkDisabled?: boolean;
}>();

const emit = defineEmits<{
  showComments: [];
}>();

const showEnlargedImage: (src: string) => void = inject('showEnlargedImage');

const updatePosts: (id: number) => Promise<void> = inject('updatePosts');

const truncate = ref(true);

const likeCount = computed((): number => props.note.likes_count);

const isLiked = computed((): boolean => props.note.likes.some((like) => like.user_id === usePage().props.auth.user.id));

const previewImagePath = computed(() => {
  return props.note.image_path ? '/storage/' + props.note.image_path : null;
});

const arrCommentLines = computed(() => splitByNewline(props.note.content ?? ''));

const isTruncated = computed(() => truncate.value && arrCommentLines.value.length > 5);

const paragraphs = computed(() => {
  let lines = arrCommentLines.value;
  if (truncate.value && lines.length > 5) {
    lines = lines.slice(0, 4);
    lines[lines.length - 1] += '...';
  }
  return lines;
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
    user: userId
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
      <p v-for="paragraph in paragraphs" class="note-paragraph text-body-1">{{ paragraph }}</p>
      <v-btn v-if="isTruncated" class="text-capitalize ps-0" color="primary" variant="text" density="compact"
        @click="truncate = false">Show more</v-btn>
      <v-img v-if="previewImagePath" :src="previewImagePath" width="300" class="mt-3 cursor-pointer" style="z-index: 1;"
        lazy-src="/lazy-src.gif" @click="showEnlargedImage(previewImagePath)">
        <template v-slot:placeholder>
          <div class="d-flex align-center justify-center fill-height">
            <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
          </div>
        </template>
      </v-img>
    </v-card-text>
    <v-card-actions>
      <v-list-item @click="showSelectedUserPosts(note.user.id)">
        <template v-slot:prepend>
          <v-avatar color="grey-darken-3" size="small" style="z-index: 1;">
            <v-img v-if="note.user.image_path" :src="'/storage/' + note.user.image_path" />
            <v-icon v-else icon="mdi-account" />
          </v-avatar>
        </template>
        <v-list-item-title>{{ note.user.name }}</v-list-item-title>
        <v-list-item-subtitle v-if="note.tag" class="text-caption">{{ note.tag?.name }}</v-list-item-subtitle>
      </v-list-item>
      <v-spacer />
      <v-btn prepend-icon="mdi-comment-outline" class="hidden-xs" @click="$emit('showComments')"
        :readonly="Boolean(commentLinkDisabled)">
        {{ note.comments_count || '' }}
        <v-tooltip activator="parent" location="bottom" text="Show comments" />
      </v-btn>
      <v-btn :prepend-icon="isLiked ? 'mdi-heart' : 'mdi-heart-outline'" class="hidden-xs"
        :class="{ 'text-pink': isLiked }" @click="like">
        {{ likeCount }}
        <v-tooltip activator="parent" location="bottom" :text="isLiked ? 'Unlike' : 'Like'" />
      </v-btn>
    </v-card-actions>
    <v-card-actions class="hidden-sm-and-up">
      <v-spacer />
      <v-btn prepend-icon="mdi-comment-outline" @click="$emit('showComments')" :readonly="Boolean(commentLinkDisabled)">
        {{ note.comments_count || '' }}
        <v-tooltip activator="parent" location="bottom" text="Show comments" />
      </v-btn>
      <v-btn :prepend-icon="isLiked ? 'mdi-heart' : 'mdi-heart-outline'" :class="{ 'text-pink': isLiked }"
        @click="like">
        {{ likeCount }}
        <v-tooltip activator="parent" location="bottom" :text="isLiked ? 'Unlike' : 'Like'" />
      </v-btn>
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