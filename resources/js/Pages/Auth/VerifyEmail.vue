<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps < {
  status?: string;
} > ();

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const submit = () => {
  form.post(route('verification.send'));
};

const logout = () => {
  router.post('logout');
};
</script>

<template>
  <GuestLayout>

    <Head title="Email Verification" />
    <v-card class="mx-auto pa-12 pb-8" elevation="8" max-width="448" rounded="lg">
      <v-alert v-if="verificationLinkSent"
        text="A new verification link has been sent to the email address you provided during registration."
        class="mb-6 " type="success" variant="tonal" closable />
      <v-card class="mb-6" color="surface-variant" variant="tonal">
        <v-card-text class="text-medium-emphasis text-caption">
          <p>Thanks for signing up! </p>
          <p>Before getting started, could you verify your email address by clicking on the
            link
            we just emailed to you?</p>
          <p>If you didn't receive the email, we will gladly send you another.</p>
        </v-card-text>
      </v-card>

      <form @submit.prevent="submit">
        <v-btn color="primary" size="large" variant="tonal" :class="{ 'text-disabled': form.processing }"
          :disabled="form.processing" block @click="submit">
          Resend Verification Email
        </v-btn>
        <v-card-text class="text-center">
          <a class="text-primary text-decoration-none" rel="noopener noreferrer" href="#" @click.prevent="logout">
            Log Out <v-icon icon="mdi-chevron-right" />
          </a>
        </v-card-text>
      </form>
    </v-card>
  </GuestLayout>
</template>
