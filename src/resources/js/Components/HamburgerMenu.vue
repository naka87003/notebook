<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useCurrentUser } from '@/Composables/useCurrentUser';

defineProps<{
  currentPageName: string;
}>();

defineEmits<{
  logout: [];
  close: [];
}>();

const isDark = defineModel<boolean>('isDark');

const { avatarImagePath, userName, userEmail } = useCurrentUser();

const pageTransition = (name: string) => {
  router.visit(route(name));
};
</script>

<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-title-large" text="Menu"></v-toolbar-title>
      <template #append>
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
        <v-list-item
          title="Notes"
          density="compact"
          :active="currentPageName === 'dashboard'"
          @click="pageTransition('dashboard')"
        >
          <template #prepend>
            <v-avatar density="compact">
              <v-icon>mdi-note-multiple-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item
          title="Calendar"
          density="compact"
          :active="currentPageName === 'calendar'"
          @click="pageTransition('calendar')"
        >
          <template #prepend>
            <v-avatar density="compact">
              <v-icon>mdi-calendar-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item
          title="Timeline"
          density="compact"
          :active="currentPageName === 'timeline'"
          @click="pageTransition('timeline')"
        >
          <template #prepend>
            <v-avatar density="compact">
              <v-icon>mdi-timeline-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item
          title="Tags"
          density="compact"
          :active="currentPageName === 'tags.index'"
          @click="pageTransition('tags.index')"
        >
          <template #prepend>
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
        <v-divider />
        <v-list-subheader>Account</v-list-subheader>
        <v-list-item
          :title="userName"
          :subtitle="userEmail"
          density="compact"
          :active="currentPageName === 'profile.edit'"
          @click="pageTransition('profile.edit')"
        >
          <template #prepend>
            <v-avatar color="surface-light" density="compact">
              <v-img v-if="avatarImagePath" :src="avatarImagePath" />
              <v-icon v-else icon="mdi-account" />
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item
          title="Preferences"
          density="compact"
          :active="currentPageName === 'preferences.edit'"
          @click="pageTransition('preferences.edit')"
        >
          <template #prepend>
            <v-avatar density="compact">
              <v-icon>mdi-cog-outline</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <v-list-item title="Logout" density="compact" @click="$emit('logout')">
          <template #prepend>
            <v-avatar density="compact">
              <v-icon>mdi-logout</v-icon>
            </v-avatar>
          </template>
        </v-list-item>
      </v-list>
    </v-card-text>
    <v-divider />
    <template #actions>
      <v-spacer />
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
    </template>
  </v-card>
</template>
