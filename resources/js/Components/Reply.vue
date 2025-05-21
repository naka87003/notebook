<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { relativeDateTime, splitByNewline } from '@/common';
import { Reply, User } from '@/interfaces';
import { computed, ref } from 'vue';

const props = defineProps<{
  reply: Reply;
}>();

const emit = defineEmits<{
  delete: [];
  replyAdded: [];
}>();

const form = useForm({
  reply: '',
  reply_to: props.reply.user_id,
});

const truncate = ref(true);

const display = ref({
  replyForm: false,
  replies: false,
});

const userImagePath = computed((): string | null => {
  const user = usePage().props.auth.user as User;
  return user.image_path;
});

const avatarImagePath = computed(() =>
  userImagePath.value ? '/storage/' + userImagePath.value : null
);

const isMyComment = computed((): boolean => {
  const user = usePage().props.auth.user as User;
  return props.reply.user_id === user.id;
});

const arrCommentLines = computed(() => splitByNewline(props.reply.content ?? ''));

const paragraphs = computed(() => {
  let lines = arrCommentLines.value;
  if (truncate.value && lines.length > 10) {
    lines = lines.slice(0, 9);
    lines[lines.length - 1] += '...';
  }
  return lines;
});

const isTruncated = computed(() => truncate.value && arrCommentLines.value.length > 10);

const showSelectedUserPosts = (userId: number) => {
  router.get(route('timeline'), {
    user: userId,
  });
};

const addReply = async () => {
  form.post(route('replies.store', props.reply.comment_id), {
    preserveScroll: true,
    onSuccess: async () => {
      form.reset();
      display.value.replyForm = false;
      emit('replyAdded');
    },
  });
};
</script>

<template>
  <v-alert density="compact" variant="text" class="pa-0">
    <template #prepend>
      <v-avatar
        color="grey-darken-3 cursor-pointer"
        size="small"
        style="z-index: 1"
        @click="showSelectedUserPosts(reply.user_id)"
      >
        <v-img v-if="reply.user.image_path" :src="'/storage/' + reply.user.image_path" />
        <v-icon v-else icon="mdi-account" />
      </v-avatar>
    </template>
    <template #title>
      <span class="text-caption text-truncate">{{ reply.user.name }}</span>
      <span class="text-caption text-no-wrap text-disabled ms-2">{{
        relativeDateTime(reply.created_at)
      }}</span>
    </template>
    <template #append>
      <v-menu v-if="isMyComment">
        <template #activator="{ props }">
          <v-icon v-bind="props" icon="mdi-dots-vertical" variant="plain" size="small" />
        </template>
        <v-list>
          <v-list-item density="compact" prepend-icon="mdi-delete-outline" @click="$emit('delete')">
            <v-list-item-title>Delete</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <p v-if="reply.reply_to" class="text-caption text-primary mt-n1">
      {{ '@ ' + reply.addressee.name }}
    </p>
    <p v-for="(paragraph, index) in paragraphs" :key="index" class="text-body-2">
      {{ paragraph }}
    </p>
    <v-btn
      v-if="isTruncated"
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
  </v-alert>
  <v-card v-show="display.replyForm" density="compact" variant="text">
    <v-card-text class="pt-0 pe-0 ps-2">
      <form @submit.prevent="addReply">
        <v-textarea
          v-model="form.reply"
          density="compact"
          variant="underlined"
          :placeholder="`To ${reply.user.name}`"
          hide-details
          clearable
          auto-grow
          rows="1"
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
</template>
