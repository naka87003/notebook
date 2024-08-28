<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits<{
  close: [];
  apply: [search: string];
}>();

const props = defineProps<{ search: string | null }>();

const search = ref(props.search);

const resetFilter = () => {
  search.value = '';
  emit('apply', search.value);
};
</script>
<template>
  <v-card>
    <v-toolbar density="compact" color="transparent">
      <v-toolbar-title class="text-h6" text="Search"></v-toolbar-title>
      <template v-slot:prepend>
        <v-icon class="ms-3" icon="mdi-magnify" />
      </template>
      <template v-slot:append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text class="pa-3">
      <v-text-field v-model="search" sdensity="compact" label="Search text" variant="solo-filled" flat hide-details
        single-line clearable>
      </v-text-field>
    </v-card-text>
    <v-divider />
    <template v-slot:actions>
      <v-btn variant="plain" @click="resetFilter">Reset</v-btn>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-btn color="primary" variant="tonal" @click="$emit('apply', search)">Apply</v-btn>
    </template>
  </v-card>
</template>