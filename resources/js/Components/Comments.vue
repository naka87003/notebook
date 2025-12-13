<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, inject } from 'vue';
import axios from 'axios';
import CommentItem from '@/Components/Comment.vue';
import type { Comment, Note, User } from '@/interfaces';
import ConfirmCard from './ConfirmCard.vue';

const props = defineProps<{
  targetNote: Note;
}>();

defineEmits<{
  close: [];
}>();

const updatePosts: (id: number) => Promise<void> = inject('updatePosts');

const form = useForm({
  comment: '',
});

const dialog = ref({
  deleteConfirm: false,
});

const targetCommentId = ref();

const comments = ref(new Map<number, Comment>());

const userImagePath = computed((): string | null => {
  const user = usePage().props.auth.user as User;
  return user.image_path;
});

const avatarImagePath = computed(() =>
  userImagePath.value ? '/storage/' + userImagePath.value : null
);

onMounted(async () => {
  const result = await loadItems();
  for (const comment of result) {
    comments.value.set(comment.id, comment);
  }
});

const loadItems = async (fresh: boolean = false): Promise<Comment[]> => {
  const params = {};
  if (fresh === false) {
    params['offset'] = comments.value.size;
  }
  const response = await axios.get(route('comments', props.targetNote.id), {
    params,
  });
  return response.data;
};

const load = async ({ done }): Promise<void> => {
  const result = await loadItems();
  if (result.length > 0) {
    for (const comment of result) {
      comments.value.set(comment.id, comment);
    }
    done('ok');
  } else {
    done('empty');
  }
};

const addComment = async () => {
  comments.value.clear();
  form.post(route('comments.store', props.targetNote.id), {
    preserveScroll: true,
    onSuccess: async () => {
      form.reset('comment');
      const result = await loadItems(true);
      for (const comment of result) {
        comments.value.set(comment.id, comment);
      }
      updatePosts(props.targetNote.id);
    },
  });
};

const showDeleteConfirmDialog = (id: number): void => {
  targetCommentId.value = id;
  dialog.value.deleteConfirm = true;
};

const deleteComment = async () => {
  dialog.value.deleteConfirm = false;
  await axios
    .delete(route('comments.destroy', targetCommentId.value))
    .then(async () => {
      comments.value.delete(targetCommentId.value);
      updatePosts(props.targetNote.id);
    })
    .catch((error) => {
      console.log(error);
    });
};

const updateComment = async (id: number) => {
  const response = await axios.get(route('comments.comment', id));
  comments.value.set(id, response.data);
};
</script>

<template>
  <v-card>
    <v-toolbar density="compact" color="transparent">
      <v-toolbar-title class="text-h6" text="Comments"></v-toolbar-title>
      <template #prepend>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-arrow-left" />
          <v-tooltip activator="parent" location="bottom" text="Back" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text>
      <v-container class="pa-0">
        <v-row>
          <v-col cols="12" class="px-0">
            <slot />
          </v-col>
          <v-col cols="12" class="px-0">
            <v-card density="compact" variant="text">
              <v-card-text class="pa-0">
                <form @submit.prevent="addComment">
                  <v-textarea
                    v-model="form.comment"
                    density="compact"
                    variant="underlined"
                    placeholder="Add a comment"
                    hide-details
                    clearable
                    auto-grow
                    rows="1"
                    :error="Boolean(form.errors.comment)"
                    counter="140"
                    max-length="140"
                  >
                    <template #prepend>
                      <v-avatar color="surface-light" size="small">
                        <v-img v-if="avatarImagePath" :src="avatarImagePath" />
                        <v-icon v-else icon="mdi-account" />
                      </v-avatar>
                    </template>
                  </v-textarea>
                  <v-card-actions v-show="form.comment" class="mb-n4 pa-0">
                    <v-spacer />
                    <v-btn
                      size="small"
                      variant="tonal"
                      color="secondary"
                      class="text-capitalize"
                      :class="{
                        'text-disabled': form.processing,
                      }"
                      :disabled="form.processing"
                      @click="addComment"
                      >Comment</v-btn
                    >
                  </v-card-actions>
                </form>
              </v-card-text>
            </v-card>
            <v-infinite-scroll
              v-if="comments.size > 0"
              class="w-100 overflow-hidden py-3"
              empty-text=""
              @load="load"
            >
              <template v-for="comment in comments.values()" :key="comment.id">
                <CommentItem
                  :comment
                  @delete="showDeleteConfirmDialog(comment.id)"
                  @update-comment="updateComment(comment.id)"
                />
              </template>
            </v-infinite-scroll>
          </v-col>
        </v-row>
      </v-container>
    </v-card-text>
  </v-card>
  <v-dialog v-model="dialog.deleteConfirm" max-width="600">
    <ConfirmCard
      icon="mdi-delete-outline"
      title="Delete Comment"
      message="Are you sure you want to delete this comment?"
      description="Once the comment is deleted, it will be permanently deleted."
      confirm-btn-name="Delete"
      confirm-btn-color="error"
      @confirmed="deleteComment"
      @close="dialog.deleteConfirm = false"
    />
  </v-dialog>
</template>
