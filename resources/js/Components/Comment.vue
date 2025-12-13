<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { relativeDateTime } from '@/common';
import type { Comment, Reply, User } from '@/interfaces';
import { computed, ref } from 'vue';
import axios from 'axios';
import ReplyItem from './Reply.vue';
import ConfirmCard from './ConfirmCard.vue';
import useParagraphs from '@/Composables/useParagraphs';

const props = defineProps<{
  comment: Comment;
}>();

const emit = defineEmits<{
  delete: [];
  updateComment: [];
}>();

const form = useForm({
  reply: '',
});

const content = computed(() => props.comment.content ?? '');

const { truncate, isTruncated, paragraphs } = useParagraphs(content, 10);

const replies = ref(new Map<number, Reply>());

const dialog = ref({
  deleteConfirm: false,
});

const display = ref({
  replyForm: false,
  replies: false,
});

const targetReplyId = ref();

const userImagePath = computed((): string | null => {
  const user = usePage().props.auth.user as User;
  return user.image_path;
});

const avatarImagePath = computed(() =>
  userImagePath.value ? '/storage/' + userImagePath.value : null
);

const isMyComment = computed((): boolean => {
  const user = usePage().props.auth.user as User;
  return props.comment.user_id === user.id;
});

const showSelectedUserPosts = (userId: number) => {
  router.get(route('timeline'), {
    user: userId,
  });
};

const loadItems = async (fresh: boolean = false): Promise<Reply[]> => {
  const params = {};
  if (fresh === false) {
    params['offset'] = replies.value.size;
  }
  const response = await axios.get(route('replies', props.comment.id), {
    params,
  });
  return response.data;
};

const load = async ({ done }): Promise<void> => {
  const result = await loadItems();
  if (result.length > 0) {
    for (const reply of result) {
      replies.value.set(reply.id, reply);
    }
    done('ok');
  } else {
    done('empty');
  }
};

const addReply = async () => {
  form.post(route('replies.store', props.comment.id), {
    preserveScroll: true,
    onSuccess: async () => {
      form.reset();
      replyAdded();
    },
  });
};

const replyAdded = async () => {
  replies.value.clear();
  const result = await loadItems(true);
  for (const reply of result) {
    replies.value.set(reply.id, reply);
  }
  display.value.replies = true;
  display.value.replyForm = false;
  emit('updateComment');
};
const showDeleteConfirmDialog = (id: number): void => {
  targetReplyId.value = id;
  dialog.value.deleteConfirm = true;
};

const deleteReply = async () => {
  dialog.value.deleteConfirm = false;
  await axios
    .delete(route('replies.destroy', targetReplyId.value))
    .then(async () => {
      replies.value.delete(targetReplyId.value);
      emit('updateComment');
    })
    .catch((error) => {
      console.log(error);
    });
};
</script>

<template>
  <v-alert density="compact" variant="text" class="px-0 py-1">
    <template #prepend>
      <v-avatar
        color="grey-darken-3 cursor-pointer"
        size="small"
        style="z-index: 1"
        @click="showSelectedUserPosts(comment.user_id)"
      >
        <v-img v-if="comment.user.image_path" :src="'/storage/' + comment.user.image_path" />
        <v-icon v-else icon="mdi-account" />
      </v-avatar>
    </template>
    <template #title>
      <span class="text-caption text-truncate">{{ comment.user.name }}</span>
      <span class="text-caption text-no-wrap text-disabled ms-2">{{
        relativeDateTime(comment.created_at)
      }}</span>
    </template>
    <template #append>
      <v-menu v-if="isMyComment">
        <template #activator="{ props }">
          <v-icon v-bind="props" icon="mdi-dots-vertical" variant="plain" size="small" />
        </template>
        <v-list class="pa-0">
          <v-list-item
            density="compact"
            prepend-icon="mdi-delete-outline"
            slim
            @click="$emit('delete')"
          >
            <v-list-item-title>Delete</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <p v-for="(paragraph, index) in paragraphs" :key="index" class="text-body-2">
      {{ paragraph }}
    </p>
    <v-btn
      v-if="isTruncated"
      size="small"
      class="text-capitalize ps-0"
      color="primary"
      variant="text"
      density="compact"
      @click="truncate = false"
      >Show more</v-btn
    >
    <div>
      <v-icon
        flat
        size="x-small"
        icon="mdi-reply-outline"
        @click="display.replyForm = !display.replyForm"
      />
    </div>
    <v-card v-show="display.replyForm" density="compact" variant="text">
      <v-card-text class="pt-0 px-0">
        <form @submit.prevent="addReply">
          <v-textarea
            v-model="form.reply"
            density="compact"
            variant="underlined"
            placeholder="Add a reply"
            hide-details
            clearable
            auto-grow
            rows="1"
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
        </form>
        <v-card-actions v-show="form.reply" class="mb-n4 pa-0">
          <v-spacer />
          <v-btn
            size="small"
            variant="tonal"
            color="secondary"
            class="text-capitalize"
            :class="{ 'text-disabled': form.processing }"
            :disabled="form.processing"
            @click="addReply"
            >Reply</v-btn
          >
        </v-card-actions>
      </v-card-text>
    </v-card>
    <v-btn
      v-if="comment.replies_count"
      size="small"
      variant="text"
      :prepend-icon="display.replies ? 'mdi-chevron-up' : 'mdi-chevron-down'"
      class="text-lowercase px-0"
      color="primary"
      @click="display.replies = !display.replies"
      >{{ comment.replies_count }} replies</v-btn
    >
  </v-alert>
  <v-infinite-scroll
    v-if="display.replies"
    class="ms-10 overflow-hidden"
    empty-text=""
    @load="load"
  >
    <template v-for="reply in replies.values()" :key="reply.id">
      <ReplyItem :reply @reply-added="replyAdded" @delete="showDeleteConfirmDialog(reply.id)" />
    </template>
  </v-infinite-scroll>
  <v-dialog v-model="dialog.deleteConfirm" max-width="600">
    <ConfirmCard
      icon="mdi-delete-outline"
      title="Delete Reply"
      message="Are you sure you want to delete this reply?"
      description="Once the reply is deleted, it will be permanently deleted."
      confirm-btn-name="Delete"
      confirm-btn-color="error"
      @confirmed="deleteReply"
      @close="dialog.deleteConfirm = false"
    />
  </v-dialog>
</template>
