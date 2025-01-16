<script setup lang="ts">
import { ref } from 'vue';
import type { Sort } from '@/interfaces';

const emit = defineEmits<{
  close: [];
  apply: [newSort: Sort];
}>();

const props = defineProps<{ sort: Sort }>();

const newSort = ref({
  key: props.sort.key,
  order: props.sort.order,
});

const resetSort = () => {
  newSort.value.key = 'updated_at';
  newSort.value.order = 'desc';
  emit('apply', newSort.value);
};
</script>
<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-h6" text="Sort Menu"></v-toolbar-title>
      <template v-slot:prepend>
        <v-icon class="ms-3" icon="mdi-sort" />
      </template>
      <template v-slot:append>
        <v-btn @click="$emit('close')">
          <v-icon size="x-large" icon="mdi-close" />
          <v-tooltip activator="parent" location="bottom" text="Close" />
        </v-btn>
      </template>
    </v-toolbar>
    <v-divider />
    <v-card-text>
      <v-row>
        <v-col cols="12">
          <div class="text-subtitle-1 text-medium-emphasis">Sort key</div>
          <v-radio-group v-model="newSort.key" column hide-details="auto">
            <v-radio label="Updated (default)" value="updated_at" />
            <v-radio label="Created" value="created_at" />
            <v-radio label="Starts (Event Category Only)" value="starts_at" />
          </v-radio-group>
        </v-col>
      </v-row>
      <v-divider class="my-5" />
      <v-row>
        <v-col cols="12">
          <div class="text-subtitle-1 text-medium-emphasis">Order</div>
          <v-radio-group v-model="newSort.order" column hide-details="auto">
            <v-radio label="Descending (default)" value="desc" />
            <v-radio label="Ascending" value="asc" />
          </v-radio-group>
        </v-col>
      </v-row>
    </v-card-text>
    <v-divider />
    <template v-slot:actions>
      <v-btn variant="plain" @click="resetSort">Reset</v-btn>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-btn color="primary" variant="tonal" @click="$emit('apply', newSort)">Apply</v-btn>
    </template>
  </v-card>
</template>
