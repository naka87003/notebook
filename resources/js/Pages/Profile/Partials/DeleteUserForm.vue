<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
  password: '',
});

const visible = ref(false);

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
  form.reset();
};
</script>

<template>
  <section class="space-y-6">
    <v-card title="Delete Account">
      <v-divider />
      <v-card-text>
        <v-card color="surface-variant" variant="tonal">
          <v-card-text class="text-medium-emphasis text-caption">
            <p>Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            <p>Before deleting your account, please download any data or information that you wish to retain.</p>
          </v-card-text>
        </v-card>
      </v-card-text>
      <v-divider />
      <template v-slot:actions>
        <v-spacer />
        <v-dialog max-width="1000">
          <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="error" size="large" variant="tonal"
              :class="{ 'text-disabled': form.processing }" :disabled="form.processing">
              Delete Account
            </v-btn>
          </template>
          <template v-slot:default="{ isActive }">
            <v-card>
              <v-toolbar density="comfortable" color="transparent">
                <v-toolbar-title class="text-h6" text="Delete Account"></v-toolbar-title>
                <template v-slot:append>
                  <v-btn @click="isActive.value = false">
                    <v-icon size="x-large" icon="mdi-close" />
                    <v-tooltip activator="parent" location="bottom" text="Close" />
                  </v-btn>
                </template>
              </v-toolbar>
              <v-divider />
              <v-card-text>
                Are you sure you want to delete your account?
                <v-card class="my-3" color="surface-variant" variant="tonal">
                  <v-card-text class="text-medium-emphasis text-caption">
                    <p>Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                    <p>please enter your password to confirm you would like to permanently delete your account.</p>
                  </v-card-text>
                </v-card>
                <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                  Password
                </div>
                <v-text-field :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'" density="compact" placeholder="Enter your password"
                  prepend-inner-icon="mdi-lock-outline" variant="outlined" v-model="form.password"
                  :error="Boolean(form.errors.password)" :error-messages="form.errors.password"
                  @click:append-inner="visible = !visible" autocomplete="new-password" required max-width="600"
                  @input="form.errors.password = null" />
              </v-card-text>
              <v-divider />
              <v-card-actions>
                <v-btn text="Cancel" color="secondary" size="large" variant="tonal"
                  @click="isActive.value = false"></v-btn>
                <v-spacer></v-spacer>
                <v-btn color="error" size="large" variant="tonal" :class="{ 'text-disabled': form.processing }"
                  :disabled="form.processing" @click="deleteUser">
                  Delete Account
                </v-btn>
              </v-card-actions>
            </v-card>
          </template>
        </v-dialog>
      </template>
    </v-card>
  </section>
</template>
