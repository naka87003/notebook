<script setup lang="ts">
import { User } from '@/interfaces';
import { usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import FollowButton from './FollowButton.vue';
import FollowerList from './FollowerList.vue';
import FolloweeList from './FolloweeList.vue';

const props = defineProps<{
  selectedUser: User;
}>();

const dialog = ref({
  followerList: false,
  followeeList: false
});

const isFollowing = ref(false);
const count = ref({
  followees: 0,
  followers: 0
});

const isMyself = computed(() => props.selectedUser.id === usePage().props.auth.user.id);

watch(() => props.selectedUser, async () => {
  await axios.get(route('users.user', props.selectedUser.id))
    .then(response => {
      count.value.followees = response.data.followees_count;
      count.value.followers = response.data.followers_count;
      isFollowing.value = response.data.followedByLoginUser;
    })
    .catch(error => {
      console.log(error);
    });
}, { immediate: true });

const follow = async () => {
  if (isMyself.value === false) {
    await axios.post(route('follows.follow', props.selectedUser.id))
      .then(function () {
        isFollowing.value = true;
        count.value.followers++;
      })
      .catch(function (error) {
        console.log(error);
      });
  }
}
const unfollow = async () => {
  await axios.delete(route('follows.unfollow', props.selectedUser.id))
    .then(function () {
      isFollowing.value = false;
      count.value.followers--;
    })
    .catch(function (error) {
      console.log(error);
    });
};
</script>

<template>
  <v-card class="mx-auto pa-1" :title="selectedUser.name" border="sm" elevation="5" rounded="xl">
    <template v-slot:prepend>
      <v-avatar color="surface-light">
        <v-img v-if="selectedUser.image_path" :src="'storage/' + selectedUser.image_path" />
        <v-icon v-else icon="mdi-account" />
      </v-avatar>
    </template>
    <v-card-text v-if="selectedUser.comment" class="ms-1 mt-2">
      {{ selectedUser.comment }}
    </v-card-text>
    <v-divider class="mb-1 hidden-sm-and-up" :class="{ 'hidden-xs': isMyself }" />
    <v-card-actions class="hidden-sm-and-up" :class="{ 'hidden-xs': isMyself }">
      <v-btn class="text-capitalize ms-1" @click="dialog.followeeList = true">
        {{ count.followees }} Following
      </v-btn>
      <v-btn class="text-capitalize" @click="dialog.followerList = true">
        {{ count.followers }} Followers
      </v-btn>
    </v-card-actions>
    <v-divider class="mb-1" />
    <v-card-actions>
      <v-btn class="text-capitalize ms-1" :class="{ 'hidden-xs': isMyself === false }"
        @click="dialog.followeeList = true">
        {{ count.followees }} Following
      </v-btn>
      <v-btn class="text-capitalize" :class="{ 'hidden-xs': isMyself === false }" @click="dialog.followerList = true">
        {{ count.followers }} Followers
      </v-btn>
      <v-spacer></v-spacer>
      <FollowButton v-if="isMyself === false" :isFollowing @follow="follow" @unfollow="unfollow" />
    </v-card-actions>
  </v-card>
  <v-dialog v-model="dialog.followerList" maxWidth="600px" scrollable>
    <FollowerList :selectedUser @close="dialog.followerList = false" />
  </v-dialog>
  <v-dialog v-model="dialog.followeeList" maxWidth="600px" scrollable>
    <FolloweeList :selectedUser @close="dialog.followeeList = false" />
  </v-dialog>
</template>