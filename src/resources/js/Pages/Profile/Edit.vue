<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateAvatarImageForm from './Partials/UpdateAvatarImageForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
  mustVerifyEmail: boolean;
  status?: string;
}>();
</script>

<template>
  <Head title="Profile" />
  <AuthenticatedLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <UpdateAvatarImageForm />
        </v-col>
        <v-col cols="12">
          <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status />
        </v-col>
        <v-col cols="12">
          <UpdatePasswordForm />
        </v-col>
        <v-col cols="12">
          <DeleteUserForm />
        </v-col>
      </v-row>
    </v-container>
  </AuthenticatedLayout>
</template>
