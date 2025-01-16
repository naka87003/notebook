<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

defineProps<{ status?: string }>();

const form = useForm({
  email: '',
});

const windowWidth = ref(1000);

const isExtraSmallWidth = computed(() => windowWidth.value < 500);

onMounted(() => {
  window.addEventListener('resize', resizeWindow);
  resizeWindow();
});

const resizeWindow = () => {
  windowWidth.value = window.innerWidth;
};

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <GuestLayout>
    <Head title="Forgot Password" />
    <v-card
      class="mx-auto"
      elevation="8"
      :max-width="isExtraSmallWidth ? '350' : '448'"
      rounded="lg"
    >
      <v-toolbar density="comfortable" color="transparent">
        <v-toolbar-title class="text-h6"> Password Reset </v-toolbar-title>
      </v-toolbar>
      <v-divider />
      <v-card-text class="pb-8" :class="isExtraSmallWidth ? 'px-6' : 'px-12'">
        <v-alert
          v-if="status"
          :text="status"
          class="mb-6"
          type="success"
          variant="tonal"
          closable
        />
        <v-card class="mb-6" color="surface-variant" variant="tonal">
          <v-card-text class="text-medium-emphasis text-caption">
            <p>Forgot your password? No problem.</p>
            <p>
              Just let us know your email address and we will email you a password reset link that
              will allow you to choose a new one.
            </p>
          </v-card-text>
        </v-card>
        <form @submit.prevent="submit">
          <div>
            <div class="text-subtitle-1 text-medium-emphasis">Email</div>
            <v-text-field
              v-model="form.email"
              type="email"
              density="compact"
              placeholder="Email address"
              prepend-inner-icon="mdi-email-outline"
              variant="outlined"
              :error="Boolean(form.errors.email)"
              :error-messages="form.errors.email"
              required
              autofocus
              autocomplete="username"
              @input="form.errors.email = null"
            />
          </div>
          <v-btn
            color="primary"
            size="large"
            variant="tonal"
            :class="{ 'text-disabled': form.processing }"
            :disabled="form.processing"
            block
            @click="submit"
          >
            Email Password Reset
          </v-btn>
        </form>
      </v-card-text>
    </v-card>
  </GuestLayout>
</template>
