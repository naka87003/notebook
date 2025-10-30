<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const visible = ref(false);

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
      }
      if (form.errors.current_password) {
        form.reset('current_password');
      }
    },
  });
};
</script>

<template>
  <section>
    <v-card title="Update Password">
      <v-divider />
      <v-card-text>
        <v-card class="mb-6" color="surface-variant" variant="tonal">
          <v-card-text class="text-medium-emphasis text-caption">
            <p>Ensure your account is using a long, random password to stay secure.</p>
          </v-card-text>
        </v-card>
        <form class="mt-6 space-y-6" @submit.prevent="updatePassword">
          <div
            class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between"
          >
            Current Password
          </div>
          <v-text-field
            v-model="form.current_password"
            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
            :type="visible ? 'text' : 'password'"
            density="compact"
            placeholder="Enter your password"
            prepend-inner-icon="mdi-lock-outline"
            variant="outlined"
            :error="Boolean(form.errors.current_password)"
            :error-messages="form.errors.current_password"
            autocomplete="current-password"
            required
            max-width="600"
            @click:append-inner="visible = !visible"
            @input="form.errors.current_password = null"
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
            max-width="600"
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
            max-width="600"
            @click:append-inner="visible = !visible"
            @input="form.errors.password_confirmation = null"
          />
          <Transition>
            <div v-if="form.recentlySuccessful">
              <v-alert text="Saved." type="success" variant="tonal" closable />
            </div>
          </Transition>
        </form>
      </v-card-text>
      <v-divider />
      <template #actions>
        <v-spacer />
        <v-btn
          color="primary"
          size="large"
          variant="tonal"
          :class="{ 'text-disabled': form.processing }"
          :disabled="form.processing"
          @click="updatePassword"
        >
          Save
        </v-btn>
      </template>
    </v-card>
  </section>
</template>

<style scoped>
.v-leave-to {
  opacity: 0;
}

.v-leave-active {
  transition: opacity 5s;
}
</style>
