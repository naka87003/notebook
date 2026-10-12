<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { User } from '@/interfaces';
import { relativeDateTime } from '@/common';
import { onMounted, Ref, ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  selectedUser: User;
}>();

defineEmits<{
  close: [];
}>();

const items: Ref<User[]> = ref([]);

onMounted(async () => {
  const result = await loadItems();
  items.value.push(...result);
});

const loadItems = async (): Promise<User[]> => {
  const response = await axios.get(route('users.followees', props.selectedUser.id), {
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

const showSelectedUserPosts = (userId: number) => {
  router.get(route('timeline'), {
    user: userId,
  });
};
</script>

<template>
  <v-card class="mx-auto w-100">
    <v-toolbar>
      <v-toolbar-title>Following</v-toolbar-title>
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
            @click="showSelectedUserPosts(item.id)"
          >
            <template #prepend>
              <v-avatar color="surface-light">
                <v-img v-if="item.image_path" :src="'storage/' + item.image_path" />
                <v-icon v-else icon="mdi-account" />
              </v-avatar>
            </template>
            <template #title>
              <span class="text-body-2 text-truncate">{{ item.name }}</span>
              <v-spacer />
              <span class="text-caption text-no-wrap">{{ relativeDateTime(item.created_at) }}</span>
            </template>
          </v-alert>
          <v-divider />
        </template>
      </v-infinite-scroll>
    </v-card-text>
  </v-card>
</template>
