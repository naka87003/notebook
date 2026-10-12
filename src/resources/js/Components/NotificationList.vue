<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { Notification } from '@/interfaces';
import { relativeDateTime } from '@/common';
import { onMounted, Ref, ref } from 'vue';
import axios from 'axios';

defineProps<{
  unreadNotificationCount: number;
}>();

const emit = defineEmits<{
  close: [];
  markAllAsRead: [];
}>();

const items: Ref<Notification[]> = ref([]);

onMounted(async () => {
  const result = await loadItems();
  items.value.push(...result);
});

const loadItems = async (): Promise<Notification[]> => {
  const response = await axios.get(route('notifications'), {
    params: {
      offset: items.value.length,
    },
  });
  return response.data;
};

const load = async ({ done }): Promise<void> => {
  const result = await loadItems();
  if (result.length > 0) {
    items.value.push(...result);
    done('ok');
  } else {
    done('empty');
  }
};

const selectItem = async (item: Notification) => {
  await markAsRead(item.id);
  switch (item.data.type) {
    case 'follow':
      router.get(route('timeline'), { user: item.user.id });
      break;
    case 'comment':
      router.get(route('dashboard'), { note: item.data.comment.note_id });
      break;
    case 'like':
      router.get(route('dashboard'), { note: item.data.note_id });
      break;
    case 'reply':
      router.get(route('timeline'), { note: item.data.comment.note_id });
      break;
  }
};

const markAsRead = async (notificationId: string) => {
  await axios.put(route('notifications.markAsRead', notificationId));
};

const markAllAsRead = async () => {
  await axios
    .put(route('notifications.markAllAsRead'))
    .then(async () => {
      emit('markAllAsRead');
    })
    .catch((error) => {
      console.log(error);
    });
};

const titleMsg = (item: Notification) => {
  switch (item.data.type) {
    case 'follow':
      return 'followed you';
    case 'comment':
      return 'commented on your note';
    case 'like':
      return 'liked on your note';
    case 'reply':
      return 'replied to your comment';
  }
};
</script>

<template>
  <v-card class="w-100">
    <v-toolbar>
      <v-toolbar-title>Notifications</v-toolbar-title>
      <template #prepend>
        <v-icon class="ms-3" icon="mdi-bell-outline" />
      </template>
      <template #append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text class="pa-0">
      <v-alert
        v-if="items.length === 0"
        variant="text"
        class="text-center"
        text="No data available"
      />
      <v-infinite-scroll
        v-if="items.length > 0"
        class="w-100 overflow-hidden"
        empty-text=""
        @load="load"
      >
        <template v-for="item in items" :key="item.id">
          <v-alert
            class="cursor-pointer"
            density="compact"
            variant="text"
            :color="item.read_at ? '' : 'info'"
            @click="selectItem(item)"
          >
            <template #prepend>
              <v-avatar color="surface-light">
                <v-img v-if="item.user.image_path" :src="'/storage/' + item.user.image_path" />
                <v-icon v-else icon="mdi-account" />
              </v-avatar>
            </template>
            <template #title>
              <span class="text-caption text-truncate">{{ item.user.name }}</span>
              <v-spacer />
              <span class="text-caption text-no-wrap">{{ relativeDateTime(item.created_at) }}</span>
            </template>
            <span class="text-caption">{{ titleMsg(item) }}</span>
          </v-alert>
          <v-divider />
        </template>
      </v-infinite-scroll>
    </v-card-text>
    <template #actions>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-spacer></v-spacer>
      <v-btn
        color="secondary"
        variant="tonal"
        :disabled="unreadNotificationCount === 0"
        @click="markAllAsRead"
      >
        Mark All as Read
      </v-btn>
    </template>
  </v-card>
</template>
