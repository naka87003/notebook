<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const visible = ref(false);

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
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};

const pageTransition = (name: string) => {
  router.visit(route(name));
};
</script>

<template>
  <GuestLayout>
    <Head title="Register" />
    <v-card
      class="mx-auto mb-10"
      elevation="8"
      :max-width="isExtraSmallWidth ? '350' : '448'"
      rounded="lg"
    >
      <v-toolbar density="comfortable" color="transparent">
        <v-toolbar-title class="text-h6"> Register </v-toolbar-title>
      </v-toolbar>
      <v-divider />
      <v-card-text :class="isExtraSmallWidth ? 'px-6' : 'px-12'">
        <form @submit.prevent="submit">
          <div class="text-subtitle-1 text-medium-emphasis">Name</div>
          <v-text-field
            v-model="form.name"
            type="text"
            density="compact"
            placeholder="Enter your name"
            prepend-inner-icon="mdi-account-outline"
            variant="outlined"
            :error="Boolean(form.errors.name)"
            :error-messages="form.errors.name"
            required
            autofocus
            autocomplete="username"
            maxlength="20"
            @input="form.errors.name = null"
          />

          <div class="text-subtitle-1 text-medium-emphasis">Email</div>
          <v-text-field
            v-model="form.email"
            type="email"
            density="compact"
            placeholder="Enter your email address"
            prepend-inner-icon="mdi-email-outline"
            variant="outlined"
            :error="Boolean(form.errors.email)"
            :error-messages="form.errors.email"
            required
            autocomplete="username"
            @input="form.errors.email = null"
          />

          <div
            class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between"
          >
            Password
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
            autocomplete="new-password"
            required
            @click:append-inner="visible = !visible"
            @input="form.errors.password = null"
          />
          <div
            class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between"
          >
            Confirm Password
          </div>
          <v-text-field
            v-model="form.password_confirmation"
            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
            :type="visible ? 'text' : 'password'"
            density="compact"
            placeholder="Enter your password again"
            prepend-inner-icon="mdi-lock-outline"
            variant="outlined"
            :error="Boolean(form.errors.password_confirmation)"
            :error-messages="form.errors.password_confirmation"
            autocomplete="new-password"
            required
            @click:append-inner="visible = !visible"
            @input="form.errors.password_confirmation = null"
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
            Register
          </v-btn>
          <v-card-text class="text-center mt-3">
            <a
              class="text-primary text-decoration-none"
              href="#"
              rel="noopener noreferrer"
              @click.prevent="pageTransition('login')"
            >
              Already registered?
              <v-icon icon="mdi-chevron-right" />
            </a>
          </v-card-text>
        </form>
      </v-card-text>
    </v-card>
  </GuestLayout>
</template>
