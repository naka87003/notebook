<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ConfirmCard from '@/Components/ConfirmCard.vue';

const form = useForm({
  image: null,
});

const dialog = ref({
  deleteConfirm: false,
});

const alertMsg = ref();
const preview = ref();

const previewImagePath = computed(() => {
  if (form.image) {
    return URL.createObjectURL(preview.value.files[0]);
  } else {
    return userImagePath.value ? 'storage/' + userImagePath.value : null;
  }
});

const userImagePath = computed((): string | null => usePage().props.auth.user.image_path);

const uploadImage = () => {
  form.post(route('profile.upload'), {
    onSuccess: () => {
      alertMsg.value = 'Uploaded.';
      form.image = null;
    },
  });
};

const deleteImage = () => {
  dialog.value.deleteConfirm = false;
  form.delete(route('profile.delete.image'), {
    onSuccess: () => {
      alertMsg.value = 'Deleted.';
      form.image = null;
    },
  });
};
</script>

<template>
  <section>
    <v-card title="Avatar Image">
      <v-divider />
      <v-card-text>
        <v-card class="mb-6" color="surface-variant" variant="tonal">
          <v-card-text class="text-medium-emphasis text-caption">
            <p>Upload your image to use as your avatar.</p>
          </v-card-text>
        </v-card>
        <v-list-item class="mb-5">
          <template v-slot:prepend>
            <v-avatar color="surface-light">
              <v-img v-if="previewImagePath" :src="previewImagePath" />
              <v-icon v-else icon="mdi-account" />
            </v-avatar>
          </template>
          <v-list-item-title>{{ $page.props.auth.user.name }}</v-list-item-title>
        </v-list-item>
        <form @submit.prevent="form.patch(route('profile.update'))" enctype="multipart/form-data">
          <div class="text-subtitle-1 text-medium-emphasis">Image Upload</div>
          <v-file-input
            ref="preview"
            v-model="form.image"
            density="compact"
            label="New image file input"
            variant="outlined"
            :error="Boolean(form.errors.image)"
            :error-messages="form.errors.image"
            required
            max-width="600"
            accept="image/png, image/jpeg"
            @update:modelValue="form.errors.image = null"
          />
        </form>
        <Transition>
          <div v-if="form.recentlySuccessful">
            <v-alert :text="alertMsg" type="success" variant="tonal" closable />
          </div>
        </Transition>
      </v-card-text>
      <v-divider />
      <template v-slot:actions>
        <v-spacer />
        <v-btn
          color="primary"
          size="large"
          variant="tonal"
          :class="{ 'text-disabled': form.processing }"
          :disabled="form.processing"
          @click="uploadImage"
        >
          Upload
        </v-btn>
        <v-btn
          color="error"
          size="large"
          variant="tonal"
          :class="{ 'text-disabled': form.processing }"
          :disabled="form.processing || $page.props.auth.user.image_path === null"
          @click="dialog.deleteConfirm = true"
        >
          Delete
        </v-btn>
      </template>
    </v-card>
  </section>
  <v-dialog v-model="dialog.deleteConfirm" max-width="600">
    <ConfirmCard
      icon="mdi-delete-outline"
      title="Delete Avatar Image"
      message="Are you sure you want to delete your avatar image?"
      description="Once the image is deleted, it will be permanently deleted."
      confirmBtnName="Delete"
      confirmBtnColor="error"
      @confirmed="deleteImage"
      @close="dialog.deleteConfirm = false"
    />
  </v-dialog>
</template>
