<script setup lang="ts">
import { ref } from 'vue';
import ConfirmCard from './ConfirmCard.vue';

defineProps<{
  isFollowing: boolean;
}>();

const emit = defineEmits<{
  follow: [];
  unfollow: [];
}>();

const dialog = ref({
  unfollowConfirm: false,
});

const btnStyle = ref({
  color: 'secondary',
  text: 'Following',
});
const changeBtnStyle = (e: MouseEvent) => {
  if (e.type === 'mouseover') {
    btnStyle.value.color = 'error';
    btnStyle.value.text = 'Unfollow';
  } else {
    btnStyle.value.color = 'secondary';
    btnStyle.value.text = 'Following';
  }
};

const confirmUnfollow = () => {
  dialog.value.unfollowConfirm = false;
  emit('unfollow');
};
</script>

<template>
  <v-btn
    v-if="isFollowing"
    :color="btnStyle.color"
    :text="btnStyle.text"
    class="me-3"
    variant="tonal"
    @click="dialog.unfollowConfirm = true"
    @mouseover="changeBtnStyle"
    @mouseleave="changeBtnStyle"
  />
  <v-btn v-else color="primary" class="me-3" variant="outlined" @click="$emit('follow')"
    >Follow</v-btn
  >
  <v-dialog v-model="dialog.unfollowConfirm" max-width="600">
    <ConfirmCard
      title="Unfollow"
      message="Are you sure you want to unfollow this user?"
      confirm-btn-name="Unfollow"
      confirm-btn-color="error"
      @confirmed="confirmUnfollow"
      @close="dialog.unfollowConfirm = false"
    />
  </v-dialog>
</template>
