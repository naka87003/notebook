<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';
import { useDark } from '@vueuse/core';
import type { User } from '@/interfaces';
import HumburgerMenu from '@/Components/HumburgerMenu.vue';
import NotificationList from '@/Components/NotificationList.vue';

const theme = useTheme();
const isDark = useDark();

const dialog = ref({
  humburgerMenu: false,
  notifications: false,
});

const unreadNotificationCount = ref(usePage().props.unreadNotificationCount as number);

const currentPageName = computed({
  get() {
    return route().current();
  },
  set() {},
});

const userImagePath = computed((): string | null => {
  const user = usePage().props.auth.user as User;
  return user.image_path;
});

const avatarImagePath = computed(() =>
  userImagePath.value ? '/storage/' + userImagePath.value : null
);

watch(
  isDark,
  (value) => {
    theme.global.name.value = value ? 'dark' : 'light';
  },
  { immediate: true }
);

const logout = () => {
  router.post('logout');
};

const pageTransition = (name: string) => {
  router.visit(route(name));
};

const markAllAsRead = () => {
  dialog.value.notifications = false;
  unreadNotificationCount.value = 0;
};
</script>

<template>
  <v-layout>
    <v-app-bar :extended="Boolean($slots.action)" class="opacity-90" scroll-behavior="hide">
      <template v-if="$slots.action" #extension>
        <v-container class="mx-auto d-flex align-center justify-center">
          <slot name="action" />
        </v-container>
      </template>
      <v-container class="d-flex align-center justify-center" fluid>
        <v-tabs v-model="currentPageName" class="hidden-xs">
          <v-tab
            value="dashboard"
            prepend-icon="mdi-note-multiple-outline"
            @click="pageTransition('dashboard')"
            >Notes</v-tab
          >
          <v-tab
            value="calendar"
            prepend-icon="mdi-calendar-outline"
            @click="pageTransition('calendar')"
            >Calendar</v-tab
          >
          <v-tab
            value="timeline"
            prepend-icon="mdi-timeline-outline"
            @click="pageTransition('timeline')"
            >Timeline</v-tab
          >
        </v-tabs>
        <v-spacer />
        <v-btn
          class="hidden-sm-and-down"
          stacked
          :active="currentPageName === 'tags.index'"
          @click="pageTransition('tags.index')"
        >
          <v-icon icon="mdi-tag-multiple-outline" />
          <v-tooltip activator="parent" location="bottom" text="Tag" />
        </v-btn>
        <v-btn class="hidden-sm-and-down" stacked @click="isDark = !isDark">
          <v-icon :icon="isDark ? 'mdi-weather-night' : 'mdi-weather-sunny'" />
          <v-tooltip activator="parent" location="bottom" text="Theme" />
        </v-btn>
        <v-btn stacked @click="dialog.notifications = true">
          <v-badge
            :model-value="unreadNotificationCount > 0"
            color="info"
            :content="unreadNotificationCount"
          >
            <v-icon icon="mdi-bell-outline" />
          </v-badge>
          <v-tooltip activator="parent" location="bottom" text="Notification" />
        </v-btn>
        <v-menu class="hidden-sm-and-down">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" stacked class="hidden-sm-and-down">
              <v-avatar color="surface-light cursor-pointer" size="small">
                <v-img v-if="avatarImagePath" :src="avatarImagePath" />
                <v-icon v-else icon="mdi-account" />
              </v-avatar>
              <v-tooltip activator="parent" location="bottom" text="Account" />
            </v-btn>
          </template>
          <v-list>
            <v-list-item class="hidden-sm-and-down">
              <v-list-item-title>
                {{ $page.props.auth.user.name }}
              </v-list-item-title>
              <v-list-item-subtitle>
                {{ $page.props.auth.user.email }}
              </v-list-item-subtitle>
              <template v-slot:prepend>
                <v-avatar color="surface-light">
                  <v-img v-if="avatarImagePath" :src="avatarImagePath" />
                  <v-icon v-else icon="mdi-account" />
                </v-avatar>
              </template>
            </v-list-item>
            <v-divider class="my-3" />
            <v-list-item
              @click="pageTransition('profile.edit')"
              :active="route().current() === 'profile.edit'"
              prepend-icon="mdi-account-box-edit-outline"
              slim
            >
              <v-list-item-title>Edit Profile</v-list-item-title>
            </v-list-item>
            <v-list-item
              @click="pageTransition('preferences.edit')"
              :active="route().current() === 'preferences.edit'"
              prepend-icon="mdi-cog-outline"
              slim
            >
              <v-list-item-title>Preferences</v-list-item-title>
            </v-list-item>
            <v-list-item @click="logout" prepend-icon="mdi-logout" slim>
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
        <v-btn
          class="hidden-md-and-up"
          stacked
          @click="dialog.humburgerMenu = !dialog.humburgerMenu"
        >
          <v-icon icon="mdi-menu" />
          <v-tooltip activator="parent" location="bottom" text="Menu" />
        </v-btn>
      </v-container>
    </v-app-bar>
    <v-main>
      <v-dialog v-model="dialog.humburgerMenu" scrollable>
        <HumburgerMenu
          :currentPageName
          v-model:isDark="isDark"
          @logout="logout"
          @close="dialog.humburgerMenu = false"
        />
      </v-dialog>
      <v-dialog v-model="dialog.notifications" maxWidth="600px" scrollable>
        <NotificationList
          :unreadNotificationCount
          @close="dialog.notifications = false"
          @markAllAsRead="markAllAsRead"
        />
      </v-dialog>
      <slot />
    </v-main>
  </v-layout>
</template>
