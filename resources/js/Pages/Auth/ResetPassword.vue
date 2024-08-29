<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
  email: string;
  token: string;
}>();

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const windowWidth = ref(1000)

const isExtraSmallWidth = computed(() => windowWidth.value < 500);

onMounted(() => {
  window.addEventListener('resize', resizeWindow)
  resizeWindow();
});

const resizeWindow = () => {
  windowWidth.value = window.innerWidth;
};

const visible = ref(false);

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Reset Password" />
    <v-card class="mx-auto" elevation="8" :max-width="isExtraSmallWidth ? '350' : '448'" rounded="lg">
      <v-toolbar density="comfortable" color="transparent">
        <v-toolbar-title class="text-h6">
          Password Reset
        </v-toolbar-title>
      </v-toolbar>
      <v-divider />
      <v-card-text class="pb-8" :class="isExtraSmallWidth ? 'px-6' : 'px-12'">
        <form @submit.prevent="submit">
          <div class="text-subtitle-1 text-medium-emphasis">Email</div>
          <v-text-field v-model="form.email" type="email" density="compact" placeholder="Enter your email address"
            prepend-inner-icon="mdi-email-outline" variant="outlined" :error="Boolean(form.errors.email)"
            :error-messages="form.errors.email" required autofocus autocomplete="username"
            @input="form.errors.email = null" />
          <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
            Password
          </div>
          <v-text-field :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'" :type="visible ? 'text' : 'password'"
            density="compact" placeholder="Enter your password" prepend-inner-icon="mdi-lock-outline" variant="outlined"
            v-model="form.password" :error="Boolean(form.errors.password)" :error-messages="form.errors.password"
            @click:append-inner="visible = !visible" autocomplete="new-password" required
            @input="form.errors.password = null" />

          <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
            Confirm Password
          </div>
          <v-text-field :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'" :type="visible ? 'text' : 'password'"
            density="compact" placeholder="Enter your password again" prepend-inner-icon="mdi-lock-outline"
            variant="outlined" v-model="form.password_confirmation" :error="Boolean(form.errors.password_confirmation)"
            :error-messages="form.errors.password_confirmation" @click:append-inner="visible = !visible"
            autocomplete="new-password" required @input="form.errors.password_confirmation = null" />
          <v-btn color="primary" size="large" variant="tonal" :class="{ 'text-disabled': form.processing }"
            :disabled="form.processing" block @click="submit">
            Reset Password
          </v-btn>
        </form>
      </v-card-text>
    </v-card>
  </GuestLayout>
</template>


