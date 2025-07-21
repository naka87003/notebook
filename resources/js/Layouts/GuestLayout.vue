<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { useTheme } from 'vuetify';
import { useDark } from '@vueuse/core';
import { watch } from 'vue';

const theme = useTheme();
const isDark = useDark();

watch(
  isDark,
  (value) => {
    theme.change(value ? 'dark' : 'light');
  },
  { immediate: true }
);
</script>

<template>
  <div class="bg">
    <ApplicationLogo />
    <slot />
  </div>
</template>

<style scoped>
.bg {
  width: 100%;
  min-height: 100vh;
  position: absolute;
  top: 0;
  left: 0;
  background: url('$image/notebook.jpeg');
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
}
</style>
