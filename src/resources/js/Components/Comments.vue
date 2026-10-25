<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import CommentItem from '@/Components/Comment.vue';
import ConfirmCard from '@/Components/ConfirmCard.vue';
import { useCurrentUser } from '@/Composables/useCurrentUser';
import { refreshNoteKey } from '@/Keys/notes';
import type { Comment, Note } from '@/Types/interfaces';
import type { InfiniteScrollLoadOptions } from '@/Types/infiniteScroll';

const props = defineProps<{
  targetNote: Note;
}>();

defineEmits<{
  close: [];
}>();

const refreshNote = inject(refreshNoteKey);

const form = useForm({
  comment: '',
});

const dialog = ref({
  deleteConfirm: false,
});

const targetCommentId = ref<number | null>(null);

const comments = ref(new Map<number, Comment>());

const { avatarImagePath } = useCurrentUser();

onMounted(async () => {
  const result = await loadItems();
  for (const comment of result) {
    comments.value.set(comment.id, comment);
  }
});

const loadItems = async (fresh = false): Promise<Comment[]> => {
  const { data } = await axios.get<Comment[]>(route('comments', props.targetNote.id), {
    params: fresh ? undefined : { offset: comments.value.size },
  });
  return data;
};

const load = async ({ done }: InfiniteScrollLoadOptions): Promise<void> => {
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
      await refreshNote?.(props.targetNote.id);
    },
  });
};

const showDeleteConfirmDialog = (id: number) => {
  targetCommentId.value = id;
  dialog.value.deleteConfirm = true;
};

const deleteComment = async (): Promise<void> => {
  if (targetCommentId.value === null) {
    return;
  }

  const commentId = targetCommentId.value;
  dialog.value.deleteConfirm = false;
  await axios
    .delete(route('comments.destroy', commentId))
    .then(async () => {
      comments.value.delete(commentId);
      await refreshNote?.(props.targetNote.id);
    })
    .catch((error) => {
      console.log(error);
    });
};

const updateComment = async (id: number): Promise<void> => {
  const response = await axios.get<Comment>(route('comments.comment', id));
  comments.value.set(id, response.data);
};
</script>

<template>
  <v-card>
    <v-toolbar density="compact" color="transparent">
      <v-toolbar-title class="text-title-large" text="Comments"></v-toolbar-title>
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
                    maxlength="140"
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
