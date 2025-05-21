<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits<{
  close: [];
  tagCreated: [];
}>();

const form = useForm({
  name: '',
  color: null,
});

const specifyColor = ref(false);

const switchColorValue = () => {
  if (specifyColor.value) {
    form.color = '#000000';
  } else {
    form.color = null;
  }
};

const submit = () => {
  form.post(route('tags.store'), {
    onSuccess: () => {
      emit('tagCreated');
    },
  });
};
</script>
<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-h6" text="Create New Tag"></v-toolbar-title>
      <template #prepend>
        <v-icon class="ms-3" icon="mdi-tag-plus-outline" />
      </template>
      <template #append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text>
      <form @submit.prevent="submit">
        <v-row>
          <v-col cols="12">
            <div class="text-subtitle-1 text-medium-emphasis">Name</div>
            <v-text-field
              v-model="form.name"
              hide-details="auto"
              type="text"
              density="compact"
              placeholder="Enter New Tag Name"
              variant="outlined"
              :error="Boolean(form.errors.name)"
              :error-messages="form.errors.name"
              required
              max-length="20"
              @input="form.errors.name = null"
            />
          </v-col>
          <v-col cols="12">
            <div class="text-subtitle-1 text-medium-emphasis">Color</div>
            <v-radio-group
              v-model="specifyColor"
              hide-details="auto"
              column
              class="mb-2"
              @update:model-value="switchColorValue"
            >
              <v-radio label="None" :value="false" />
              <v-radio label="Specify Color" :value="true" />
            </v-radio-group>
            <v-text-field
              v-if="specifyColor"
              v-model="form.color"
              hide-details="auto"
              type="color"
              density="compact"
              placeholder="Select Color"
              variant="outlined"
              :error="Boolean(form.errors.color)"
              :error-messages="form.errors.color"
              required
              @input="form.errors.color = null"
            />
          </v-col>
        </v-row>
      </form>
    </v-card-text>
    <v-divider />
    <template #actions>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-btn color="primary" variant="tonal" :disabled="form.processing" @click="submit">Add</v-btn>
    </template>
  </v-card>
</template>
