<script setup lang="ts">
const {
  title = 'Title',
  message = 'Message',
  confirmBtnName = 'Confirm',
} = defineProps<{
  icon?: string;
  title?: string;
  message?: string;
  description?: string;
  confirmBtnName?: string;
  confirmBtnColor?: string;
  variant?: string;
}>();

defineEmits<{
  close: [];
  confirmed: [];
}>();
</script>
<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-h6" :text="title"></v-toolbar-title>
      <template v-if="icon" #prepend>
        <v-icon class="ms-3" :icon />
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
      {{ message }}
      <v-card v-if="description" class="ma-2" color="surface-variant" variant="tonal">
        <v-card-text class="text-medium-emphasis text-caption">
          <p>{{ description }}</p>
        </v-card-text>
      </v-card>
    </v-card-text>
    <v-divider />
    <template #actions>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Cancel</v-btn>
      <v-btn :color="confirmBtnColor" variant="tonal" @click="$emit('confirmed')">
        {{ confirmBtnName }}
      </v-btn>
    </template>
  </v-card>
</template>
