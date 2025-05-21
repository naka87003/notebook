<script setup lang="ts">
import { type Ref, ref } from 'vue';
import type { PostsFilter } from '@/interfaces';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { User } from '@/interfaces';

defineEmits<{
  close: [];
  apply: [user: number];
}>();

const props = defineProps<{ filter: PostsFilter }>();

const userItems = defineModel<User[]>('userItems');

const message = ref('Please enter any text.');

const user: Ref<number> = ref(props.filter.user);

const loadUsers = useDebounceFn(async (searchText: string): Promise<void> => {
  if (searchText.length > 0) {
    message.value = 'Searching...';
    await axios
      .get(route('users.users'), {
        params: {
          search: searchText,
        },
      })
      .then((response) => {
        userItems.value = Array.from(
          new Map(
            [...userItems.value, ...response.data].map((user: User) => [user.id, user])
          ).values()
        );
        message.value = 'No data available';
      })
      .catch((error) => {
        console.log(error);
      });
  } else {
    message.value = 'Please enter any text.';
  }
}, 500);
</script>
<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-h6" text="Filter User"></v-toolbar-title>
      <template #prepend>
        <v-icon class="ms-3" icon="mdi-account-filter-outline" />
      </template>
      <template #append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text>
      <v-row>
        <v-col cols="12">
          <v-autocomplete
            v-model="user"
            hide-details="auto"
            density="compact"
            placeholder="Select User"
            variant="outlined"
            :items="userItems"
            item-title="name"
            item-value="id"
            :no-data-text="message"
            clearable
            @update:search="loadUsers"
          >
            <template #item="{ props, item }">
              <v-list-item v-bind="props" :title="item.raw.name">
                <template #prepend>
                  <v-avatar color="surface-light">
                    <v-img v-if="item.raw.image_path" :src="'storage/' + item.raw.image_path" />
                    <v-icon v-else icon="mdi-account" />
                  </v-avatar>
                </template>
              </v-list-item>
            </template>
            <template #selection="{ item }">
              <v-list-item :title="item.raw.name">
                <template #prepend>
                  <v-avatar color="surface-light" size="small">
                    <v-img v-if="item.raw.image_path" :src="'storage/' + item.raw.image_path" />
                    <v-icon v-else icon="mdi-account" />
                  </v-avatar>
                </template>
              </v-list-item>
            </template>
          </v-autocomplete>
        </v-col>
      </v-row>
    </v-card-text>
    <v-divider />
    <template #actions>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-btn color="primary" variant="tonal" @click="$emit('apply', user)">Apply</v-btn>
    </template>
  </v-card>
</template>
