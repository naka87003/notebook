<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

defineProps<{
  canResetPassword: boolean;
  status?: string;
}>();
const form = useForm({
  email: '',
  password: '',
  remember: false,
});
const visible = ref(false);

const windowWidth = ref(1000);

const isExtraSmallWidth = computed(() => windowWidth.value < 500);

onMounted(() => {
  window.addEventListener('resize', resizeWindow);
  resizeWindow();
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};

const loginAsGuest = () => {
  form.email = 'guest@example.com';
  form.password = 'password';
  submit();
};

const pageTransition = (name: string) => {
  router.visit(route(name));
};

const resizeWindow = () => {
  windowWidth.value = window.innerWidth;
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />
    <v-card
      class="mx-auto"
      elevation="8"
      :max-width="isExtraSmallWidth ? '350' : '448'"
      rounded="lg"
    >
      <v-toolbar density="comfortable" color="transparent">
        <v-toolbar-title class="text-h6"> Login </v-toolbar-title>
      </v-toolbar>
      <v-divider />
      <v-card-text :class="isExtraSmallWidth ? 'px-6' : 'px-12'">
        <v-alert
          v-if="status"
          :text="status"
          type="success"
          variant="tonal"
          class="mb-3"
          closable
        />
        <form @submit.prevent="submit" @keyup.enter="submit">
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
          <div
            class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between"
          >
            Password
            <a
              v-if="canResetPassword"
              href="#"
              class="text-caption text-decoration-none text-primary"
              rel="noopener noreferrer"
              @click.prevent="pageTransition('password.request')"
            >
              Forgot login password?</a
            >
          </div>
          <v-text-field
            v-model="form.password"
            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
            :type="visible ? 'text' : 'password'"
            density="compact"
            placeholder="Enter your password"
            prepend-inner-icon="mdi-lock-outline"
            variant="outlined"
            :error="Boolean(form.errors.password)"
            :error-messages="form.errors.password"
            autocomplete="current-password"
            required
            @click:append-inner="visible = !visible"
            @input="form.errors.password = null"
          />
          <v-btn
            color="primary"
            size="large"
            variant="tonal"
            :class="{ 'text-disabled': form.processing }"
            :disabled="form.processing"
            block
            @click="submit"
          >
            Log In
          </v-btn>
          <v-checkbox
            v-model:checked="form.remember"
            color="info"
            name="remember"
            label="Remember me"
          />
          <v-divider class="mb-5" />
          <v-btn
            color="secondary"
            prepend-icon="mdi-account"
            variant="tonal"
            :class="{ 'text-disabled': form.processing }"
            :disabled="form.processing"
            block
            @click="loginAsGuest"
          >
            Log In as guest
          </v-btn>
          <v-card-text class="text-center">
            <a
              class="text-primary text-decoration-none"
              href="#"
              rel="noopener noreferrer"
              @click.prevent="pageTransition('register')"
            >
              Sign up now <v-icon icon="mdi-chevron-right" />
            </a>
          </v-card-text>
        </form>
      </v-card-text>
    </v-card>
  </GuestLayout>
</template>
