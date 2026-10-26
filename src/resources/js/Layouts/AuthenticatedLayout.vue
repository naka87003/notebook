<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import HamburgerMenu from '@/Components/HamburgerMenu.vue';
import NotificationList from '@/Components/NotificationList.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useCurrentUser } from '@/Composables/useCurrentUser';

const page = usePage<{ unreadNotificationCount: number }>();
const { isDark } = useDarkMode();

const dialog = ref({
  hamburgerMenu: false,
  notifications: false,
});

const unreadNotificationCount = ref(page.props.unreadNotificationCount);

const currentPageName = computed<string>(() => route().current() ?? '');

const { avatarImagePath, userName, userEmail } = useCurrentUser();

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
        <v-tabs :model-value="currentPageName" class="hidden-xs">
          <v-tab
            value="notes"
            prepend-icon="mdi-note-multiple-outline"
            @click="pageTransition('notes')"
          >
            Notes
          </v-tab>
          <v-tab
            value="calendar"
            prepend-icon="mdi-calendar-outline"
            @click="pageTransition('calendar')"
          >
            Calendar
          </v-tab>
          <v-tab
            value="timeline"
            prepend-icon="mdi-timeline-outline"
            @click="pageTransition('timeline')"
          >
            Timeline
          </v-tab>
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
          <template #activator="{ props }">
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
                {{ userName }}
              </v-list-item-title>
              <v-list-item-subtitle>
                {{ userEmail }}
              </v-list-item-subtitle>
              <template #prepend>
                <v-avatar color="surface-light">
                  <v-img v-if="avatarImagePath" :src="avatarImagePath" />
                  <v-icon v-else icon="mdi-account" />
                </v-avatar>
              </template>
            </v-list-item>
            <v-divider class="my-3" />
            <v-list-item
              :active="route().current() === 'profile.edit'"
              prepend-icon="mdi-account-box-edit-outline"
              slim
              @click="pageTransition('profile.edit')"
            >
              <v-list-item-title>Edit Profile</v-list-item-title>
            </v-list-item>
            <v-list-item
              :active="route().current() === 'preferences.edit'"
              prepend-icon="mdi-cog-outline"
              slim
              @click="pageTransition('preferences.edit')"
            >
              <v-list-item-title>Preferences</v-list-item-title>
            </v-list-item>
            <v-list-item prepend-icon="mdi-logout" slim @click="logout">
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
        <v-btn
          class="hidden-md-and-up"
          stacked
          @click="dialog.hamburgerMenu = !dialog.hamburgerMenu"
        >
          <v-icon icon="mdi-menu" />
          <v-tooltip activator="parent" location="bottom" text="Menu" />
        </v-btn>
      </v-container>
    </v-app-bar>
    <v-main>
      <v-dialog v-model="dialog.hamburgerMenu" scrollable>
        <HamburgerMenu
          v-model:is-dark="isDark"
          :current-page-name
          @logout="logout"
          @close="dialog.hamburgerMenu = false"
        />
      </v-dialog>
      <v-dialog v-model="dialog.notifications" max-width="600px" scrollable>
        <NotificationList
          :unread-notification-count
          @close="dialog.notifications = false"
          @mark-all-as-read="markAllAsRead"
        />
      </v-dialog>
      <slot />
    </v-main>
  </v-layout>
</template>
