<script setup lang="ts">
import { User } from '@/interfaces';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
  currentPageName: string;
}>();

defineEmits<{
  logout: [];
  close: [];
}>();

const isDark = defineModel('isDark');

const userImagePath = computed((): string | null => {
  const user = usePage().props.auth.user as User;
  return user.image_path;
});

const avatarImagePath = computed(() => userImagePath.value ? 'storage/' + userImagePath.value : null);

const pageTransition = (name: string) => {
  router.visit(route(name));
};
</script>

<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-h6" text="Menu"></v-toolbar-title>
      <template v-slot:append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text class="pa-0">
      <v-list lines="two">
        <v-list-subheader>Page</v-list-subheader>
        <v-list-item title="Notes" density="compact" :active="currentPageName === 'dashboard'"
          @click="pageTransition('dashboard')">
          <template v-slot:prepend>
            <v-avatar density="compact">
              <v-icon>mdi-note-multiple-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item title="Calendar" density="compact" :active="currentPageName === 'calendar'"
          @click="pageTransition('calendar')">
          <template v-slot:prepend>
            <v-avatar density="compact">
              <v-icon>mdi-calendar-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item title="Timeline" density="compact" :active="currentPageName === 'timeline'"
          @click="pageTransition('timeline')">
          <template v-slot:prepend>
            <v-avatar density="compact">
              <v-icon>mdi-timeline-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item title="Tags" density="compact" :active="currentPageName === 'tags.index'"
          @click="pageTransition('tags.index')">
          <template v-slot:prepend>
            <v-avatar density="compact">
              <v-icon>mdi-tag-multiple-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-divider />
        <v-list-subheader>Theme</v-list-subheader>
        <v-list-item density="compact" class="mb-n3">
          <v-radio-group v-model="isDark">
            <v-radio :value="false">
              <template #label>
                <v-icon icon="mdi-weather-sunny mr-2"></v-icon>
                Light
              </template>
            </v-radio>
            <v-radio :value="true">
              <template #label>
                <v-icon icon="mdi-weather-night mr-2"></v-icon>
                Dark
              </template>
            </v-radio>
          </v-radio-group>
        </v-list-item>
        <v-divider></v-divider>
        <v-list-subheader>Account</v-list-subheader>
        <v-list-item :title="$page.props.auth.user.name" :subtitle="$page.props.auth.user.email" density="compact"
          :active="currentPageName === 'profile.edit'" @click="pageTransition('profile.edit')">
          <template v-slot:prepend>
            <v-avatar color="surface-light" density="compact">
              <v-img v-if="avatarImagePath" :src="avatarImagePath" />
              <v-icon v-else icon="mdi-account" />
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item title="Preferences" density="compact"
          :active="currentPageName === 'preferences.edit'" @click="pageTransition('preferences.edit')">
          <template v-slot:prepend>
            <v-avatar density="compact">
              <v-icon>mdi-cog-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item title="Logout" density="compact" @click="$emit('logout')">
          <template v-slot:prepend>
            <v-avatar density="compact">
              <v-icon>mdi-logout</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
      </v-list>
    </v-card-text>
    <v-divider />
    <template v-slot:actions>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
    </template>
  </v-card>
</template>