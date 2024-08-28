<script setup lang="ts">
import { ref, onMounted, type Ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { NotesFilter, Category, Tag } from '@/interfaces';
import { getTagSelectItems } from '@/common';

const emit = defineEmits<{
  close: [];
  apply: [newFilter: NotesFilter];
}>();

const page = usePage();

const props = defineProps<{ filter: NotesFilter }>();

const items = ref({
  category: page.props.categoryItems as Category[],
  status: [
    { id: 1, name: 'Normal', mdi_name: 'mdi-book-open-outline' },
    { id: 2, name: 'Archive', mdi_name: 'mdi-archive-outline' },
  ],
  tag: [] as Tag[]
});

const newFilter: Ref<NotesFilter> = ref({
  category: props.filter.category,
  tag: props.filter.tag,
  status: props.filter.status,
  onlyLiked: props.filter.onlyLiked
});

onMounted(async () => {
  items.value.tag = await getTagSelectItems();
});

const resetFilter = () => {
  newFilter.value.category = [1, 2, 3];
  newFilter.value.status = 1;
  newFilter.value.tag = [];
  newFilter.value.onlyLiked = false;
  emit('apply', newFilter.value);
};
</script>
<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-h6" text="Filter Menu"></v-toolbar-title>
      <template v-slot:prepend>
        <v-icon class="ms-3" icon="mdi-filter-menu-outline" />
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
          <div class="text-subtitle-1 text-medium-emphasis">Category</div>
          <v-checkbox v-for="item in items.category" v-model="newFilter.category" :value="item.id" class="mb-n10">
            <template #label>
              <v-icon :icon="item.mdi_name" class="mx-3"></v-icon>
              {{ item.name }}
            </template>
          </v-checkbox>
        </v-col>
        <v-col cols="12">
          <div class="text-subtitle-1 text-medium-emphasis">Status</div>
          <v-radio-group v-model="newFilter.status" column hide-details="auto">
            <v-radio v-for="item in items.status" :label="item.name" :value="item.id">
              <template #label>
                <v-icon :icon="item.mdi_name" class="mx-3"></v-icon>
                {{ item.name }}
              </template>
            </v-radio>
          </v-radio-group>
        </v-col>
        <v-col cols="12">
          <div class="text-subtitle-1 text-medium-emphasis">Tag</div>
          <v-autocomplete v-model="newFilter.tag" hide-details="auto" :items="items.tag" density="compact"
            placeholder="Select Tag" variant="outlined" item-title="name" item-value="id" chips closable-chips multiple>
            <template v-slot:item="{ props, item }">
              <v-list-item v-bind="props" prepend-icon="mdi-tag" :title="item.raw.name">
                <template v-slot:prepend>
                  <v-icon icon="mdi-tag" :color="item.raw.hex_color" />
                </template>
              </v-list-item>
            </template>
            <template v-slot:chip="{ props, item }">
              <v-chip v-bind="props" :text="item.raw.name" close-icon="mdi-close">
                <template v-slot:prepend>
                  <v-icon icon="mdi-tag" :color="item.raw.hex_color" size="x-large" class="mr-1" />
                </template>
              </v-chip>
            </template>
          </v-autocomplete>
        </v-col>
        <v-col cols="12">
          <div class="text-subtitle-1 text-medium-emphasis">Like</div>
          <v-checkbox v-model="newFilter.onlyLiked" class="mb-n10">
            <template #label>
              <v-icon icon="mdi-heart" class="mx-3"></v-icon>
              Show only liked notes
            </template>
          </v-checkbox>
        </v-col>
      </v-row>
    </v-card-text>
    <v-divider />
    <template v-slot:actions>
      <v-btn variant="plain" @click="resetFilter">Reset</v-btn>
      <v-spacer></v-spacer>
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-btn color="primary" variant="tonal" @click="$emit('apply', newFilter)">Apply</v-btn>
    </template>
  </v-card>
</template>