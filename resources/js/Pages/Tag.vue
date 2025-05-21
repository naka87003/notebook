<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { type Ref, ref } from 'vue';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TagCreateForm from '@/Components/TagCreateForm.vue';
import TagEditForm from '@/Components/TagEditForm.vue';
import type { Tag, TagCount } from '@/interfaces';
import ConfirmCard from '@/Components/ConfirmCard.vue';
import type { DataTableHeader } from 'vuetify';

const searchText = ref('');

const dialog = ref({
  create: false,
  edit: false,
  deleteConfirm: false,
});

const deleteConfirmDescription = ref('');

const snackbar = ref({
  display: false,
  message: '',
});

const search = ref('');
const itemsPerPage = ref(10);

const headers = ref<DataTableHeader[]>([
  { title: 'Tag', key: 'name' },
  { title: 'Normal', key: 'normal_count' },
  { title: 'Archived', key: 'archived_count' },
  { title: 'Actions', key: 'actions', sortable: false },
]);

const items: Ref<(Tag & TagCount)[]> = ref([]);
const loading = ref(true);
const totalItems = ref(0);
const targetTag = ref();

watchDebounced(searchText, () => (search.value = String(Date.now())), {
  debounce: 500,
  maxWait: 1000,
});

const loadItems = async ({ page, itemsPerPage, sortBy }) => {
  loading.value = true;
  await axios
    .get(route('tags.items.datatable'), {
      params: { page, itemsPerPage, sortBy, search: searchText.value },
    })
    .then((response) => {
      items.value = response.data.items;
      totalItems.value = response.data.count;
    })
    .catch((error) => {
      console.log(error);
    });
  loading.value = false;
};

const editItem = (item: Tag) => {
  targetTag.value = item;
  dialog.value.edit = true;
};

const tagCreated = async () => {
  search.value = String(Date.now());
  dialog.value.create = false;
  showSnackBar('Created Successfully.');
};
const tagUpdated = async () => {
  search.value = String(Date.now());
  dialog.value.edit = false;
  showSnackBar('Updated Successfully.');
};

const showSnackBar = (msg: string): void => {
  snackbar.value.message = msg;
  snackbar.value.display = true;
};

const showDeleteConfirmDialog = (item: Tag & TagCount): void => {
  targetTag.value = item;
  if (item.normal_count + item.archived_count) {
    deleteConfirmDescription.value =
      'Deleting a tag will also remove the associated tag information from any linked notes.';
  } else {
    deleteConfirmDescription.value = '';
  }
  dialog.value.deleteConfirm = true;
};

const deleteTag = async () => {
  dialog.value.deleteConfirm = false;
  await axios
    .delete(route('tags.destroy', targetTag.value.id))
    .then(async () => {
      search.value = String(Date.now());
      showSnackBar('Deleted Successfully.');
    })
    .catch((error) => {
      console.log(error);
    });
};

const showTaggedNotes = (item: Tag, status: number) => {
  router.get(route('dashboard'), {
    tag: item.id,
    status,
  });
};
</script>
<template>
  <Head title="Tag" />
  <v-snackbar v-model="snackbar.display" location="top right" color="success" timeout="3000">
    <v-icon class="me-3" style="margin-bottom: 2px">mdi-check-circle</v-icon>{{ snackbar.message }}
  </v-snackbar>
  <AuthenticatedLayout>
    <template #action>
      <v-text-field
        v-model="searchText"
        density="compact"
        label="Search"
        prepend-inner-icon="mdi-magnify"
        variant="solo-filled"
        flat
        hide-details
        single-line
      ></v-text-field>
      <v-spacer></v-spacer>
      <v-btn icon="mdi-tag-plus-outline" variant="flat" @click="dialog.create = true" />
    </template>
    <v-container>
      <v-data-table-server
        v-model:items-per-page="itemsPerPage"
        :headers
        :items
        :items-length="totalItems"
        :loading="loading"
        :search
        @update:options="loadItems"
      >
        <template #item.name="{ item }">
          <v-icon class="me-3" size="small" :color="item.hex_color" icon="mdi-tag" />
          {{ item.name }}
        </template>
        <template #item.normal_count="{ item }">
          <v-btn
            v-if="item.normal_count !== null"
            variant="plain"
            color="primary"
            @click="showTaggedNotes(item, 1)"
          >
            {{ item.normal_count }}
          </v-btn>
          <v-btn v-else variant="plain" disabled>0</v-btn>
        </template>
        <template #item.archived_count="{ item }">
          <v-btn
            v-if="item.archived_count !== null"
            variant="plain"
            color="primary"
            @click="showTaggedNotes(item, 2)"
          >
            {{ item.archived_count }}
          </v-btn>
          <v-btn v-else variant="plain" disabled>0</v-btn>
        </template>
        <template #item.actions="{ item }">
          <v-icon icon="mdi-pencil-outline" class="me-3" size="small" @click="editItem(item)" />
          <v-icon icon="mdi-delete-outline" size="small" @click="showDeleteConfirmDialog(item)" />
        </template>
      </v-data-table-server>
    </v-container>
    <v-dialog v-model="dialog.create" max-width="600">
      <TagCreateForm @close="dialog.create = false" @tag-created="tagCreated" />
    </v-dialog>
    <v-dialog v-model="dialog.edit" max-width="600">
      <TagEditForm :target-tag @close="dialog.edit = false" @tag-updated="tagUpdated" />
    </v-dialog>
    <v-dialog v-model="dialog.deleteConfirm" max-width="600">
      <ConfirmCard
        icon="mdi-delete-outline"
        title="Delete Tag"
        message="Are you sure you want to delete this tag?"
        :description="deleteConfirmDescription"
        confirm-btn-name="Delete"
        confirm-btn-color="error"
        @confirmed="deleteTag"
        @close="dialog.deleteConfirm = false"
      />
    </v-dialog>
  </AuthenticatedLayout>
</template>
