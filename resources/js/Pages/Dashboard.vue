<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, provide, type Ref } from 'vue';
import { watchDebounced } from '@vueuse/core'
import type { Note, Sort, NotesFilter } from '@/interfaces';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NoteItem from '@/Components/Note.vue';
import NoteCreateForm from '@/Components/NoteCreateForm.vue';
import NoteEditForm from '@/Components/NoteEditForm.vue';
import ConfirmCard from '@/Components/ConfirmCard.vue';
import NoteSortMenu from '@/Components/NoteSortMenu.vue';
import NoteFilterMenu from '@/Components/NoteFilterMenu.vue';
import LikedUserList from '@/Components/LikedUserList.vue';
import Comments from '@/Components/Comments.vue';
import SearchTextForm from '@/Components/SearchTextForm.vue';

const props = defineProps<{
  tag?: number;
  status?: number;
  newRegisteredUser: boolean;
  note?: Note;
}>();

const isInProgress = ref(true);

const search = ref('');

const notes = ref(new Map<number, Note>());

const dialog = ref({
  create: false,
  edit: false,
  archiveConfirm: false,
  retrieveConfirm: false,
  deleteConfirm: false,
  searchText:false,
  sortMenu: false,
  filterMenu: false,
  enlargedImage: false,
  likedUserList: false,
  noteComments: false
});
const snackbar = ref({
  display: false,
  message: ''
});

const sort: Ref<Sort> = ref({
  key: 'updated_at',
  order: 'desc'
});

const filter: Ref<NotesFilter> = ref({
  category: [1, 2, 3],
  tag: [],
  status: props.status ?? 1,
  onlyLiked: false
});

const previewImagePath = ref('');

const targetNoteId: Ref<number> = ref(null);

const targetNote = computed(() => notes.value.get(targetNoteId.value));

const searchEntered = computed((): boolean => Boolean(search.value));

const sortChanged = computed((): boolean => (sort.value.key !== 'updated_at' || sort.value.order !== 'desc'));

const sortIcon = computed((): string => {
  if (sort.value.key === 'starts_at') {
    return sort.value.order === 'asc' ? 'mdi-sort-calendar-ascending' : 'mdi-sort-calendar-descending';
  } else {
    return sort.value.order === 'asc' ? 'mdi-sort-clock-ascending-outline' : 'mdi-sort-clock-descending-outline';
  }
});

const filterChanged = computed((): boolean => (
  filter.value.category.length !== 3 ||
  filter.value.status !== 1 ||
  filter.value.tag.length !== 0 ||
  filter.value.onlyLiked === true
));

watchDebounced(search,
  () => refreshDisplay(),
  { debounce: 500, maxWait: 1000 },
);

onMounted(async () => {
  if (props.tag !== undefined) {
    filter.value.tag.push(props.tag);
  }
  const result = await loadNotes();
  for (const note of result) {
    notes.value.set(note.id, note);
  }

  if (props.note) {
    notes.value.set(props.note.id, props.note);
    showComments(props.note);
  }
  isInProgress.value = false;
});

const loadNotes = async (): Promise<Note[]> => {
  const response = await axios.get(route('notes.index'), {
    params: {
      offset: notes.value.size,
      search: search.value,
      ...sort.value,
      ...filter.value
    }
  });
  return response.data;
};

const load = async ({ done }): Promise<void> => {
  const result = await loadNotes();
  if (result.length > 0) {
    for (const note of result) {
      notes.value.set(note.id, note);
    }
    done('ok');
  } else {
    done('empty');
  }
};

const noteCreated = async () => {
  dialog.value.create = false;
  await refreshDisplay();
  showSnackBar('Created Successfully.');
};

const noteUpdated = async () => {
  dialog.value.edit = false;
  await refreshDisplay();
  showSnackBar('Updated Successfully.');
};

const showEditDialog = (note: Note): void => {
  targetNoteId.value = note.id;
  dialog.value.edit = true;
};

const showArchiveConfirmDialog = (note: Note): void => {
  targetNoteId.value = note.id;
  dialog.value.archiveConfirm = true;
};

const showRetrieveConfirmDialog = (note: Note): void => {
  targetNoteId.value = note.id;
  dialog.value.retrieveConfirm = true;
};

const showDeleteConfirmDialog = (note: Note): void => {
  targetNoteId.value = note.id;
  dialog.value.deleteConfirm = true;
};

const archiveNote = async (): Promise<void> => {
  dialog.value.archiveConfirm = false;
  await axios.put(route('notes.archive', targetNote.value.id))
    .then(async () => {
      notes.value.delete(targetNote.value.id);
      showSnackBar('Archived Successfully.');
    })
    .catch(error => {
      console.log(error);
    });
};

const retrieveNote = async (): Promise<void> => {
  dialog.value.retrieveConfirm = false;
  await axios.put(route('notes.retrieve', targetNote.value.id))
    .then(async () => {
      notes.value.delete(targetNote.value.id);
      showSnackBar('Retrieved Successfully.');
    })
    .catch(error => {
      console.log(error);
    });
};

const deleteNote = async (): Promise<void> => {
  dialog.value.deleteConfirm = false;
  await axios.delete(route('notes.destroy', targetNote.value.id))
    .then(async () => {
      notes.value.delete(targetNote.value.id);
      showSnackBar('Deleted Successfully.');
    })
    .catch(error => {
      console.log(error);
    });
};

const showSnackBar = (msg: string): void => {
  snackbar.value.message = msg;
  snackbar.value.display = true;
};

const refreshDisplay = async (): Promise<void> => {
  isInProgress.value = true;
  notes.value.clear();
  const result = await loadNotes();
  for (const note of result) {
    notes.value.set(note.id, note);
  }
  isInProgress.value = false;
};

const searchApply = (newSearch: string) => {
  dialog.value.searchText = false;
  search.value = newSearch;
};

const sortApply = async (newSort: Sort): Promise<void> => {
  dialog.value.sortMenu = false;
  sort.value.key = newSort.key;
  sort.value.order = newSort.order;
  await refreshDisplay();
};

const filterApply = async (newFilter: NotesFilter): Promise<void> => {
  dialog.value.filterMenu = false;
  filter.value.category = newFilter.category;
  filter.value.status = newFilter.status;
  filter.value.tag = newFilter.tag;
  filter.value.onlyLiked = newFilter.onlyLiked;
  await refreshDisplay();
};

const showEnlargedImage = (src: string) => {
  dialog.value.enlargedImage = true;
  previewImagePath.value = src;
};

const showLikedUserList = (note: Note) => {
  dialog.value.likedUserList = true;
  targetNoteId.value = note.id;
};

const showComments = (note: Note) => {
  targetNoteId.value = note.id;
  dialog.value.noteComments = true;
};

const updatePosts = async (id: number) => {
  const response = await axios.get(route('notes.note', id));
  notes.value.set(id, response.data);
};

provide('showEnlargedImage', showEnlargedImage);
provide('updatePosts', updatePosts);
</script>

<template>

  <Head title="Notes" />
  <v-snackbar v-model="snackbar.display" location="top right" color="success" timeout="3000">
    <v-icon class="me-3" style="margin-bottom: 2px;">mdi-check-circle</v-icon>{{ snackbar.message }}
  </v-snackbar>
  <AuthenticatedLayout>
    <template #action>
      <v-text-field v-model="search" class="hidden-xs" density="compact" label="Search" variant="solo-filled" flat
        hide-details single-line clearable>
        <template #prepend-inner>
          <v-icon icon="mdi-magnify" :class="{ 'text-red': searchEntered }" />
        </template>
      </v-text-field>
      <v-btn class="hidden-sm-and-up" @click="dialog.searchText = true">
        <v-icon size="x-large" icon="mdi-magnify" :class="{ 'text-red': searchEntered }" />
        <v-tooltip activator="parent" location="bottom" text="Search" />
      </v-btn>
      <v-spacer></v-spacer>
      <v-btn @click="dialog.create = true">
        <v-icon size="x-large" icon="mdi-plus" />
        <v-tooltip activator="parent" location="bottom" text="New" />
      </v-btn>
      <v-btn :class="{ 'text-red': sortChanged }" @click="dialog.sortMenu = true">
        <v-icon size="x-large" :icon="sortIcon" />
        <v-tooltip activator="parent" location="bottom" text="Sort" />
      </v-btn>
      <v-btn :class="{ 'text-red': filterChanged }" @click="dialog.filterMenu = true">
        <v-icon size="x-large" icon="mdi-filter-menu-outline" />
        <v-tooltip activator="parent" location="bottom" text="Filter" />
      </v-btn>
    </template>
    <v-container>
      <template v-if="newRegisteredUser">
        <v-alert icon="mdi-hand-clap" color="success" variant="tonal" class="hidden-xs">
          <v-alert-title>Welcome to Notebook, {{ $page.props.auth.user.name }}!</v-alert-title>
          Let's click the plus button at the top right to create your first note!
        </v-alert>
        <v-alert icon="mdi-hand-clap" color="success" variant="tonal" class="hidden-sm-and-up">
          <v-alert-title>Welcome, {{ $page.props.auth.user.name }}!</v-alert-title>
          Let's click the plus button above to create your first note!
        </v-alert>
      </template>
      <v-alert v-else-if="isInProgress === false && notes.size === 0" variant="text" class="text-center" text="No data available" />
      <v-infinite-scroll v-else :onLoad="load" class="w-100 overflow-hidden" empty-text="">
        <v-row>
          <template v-for="note in notes.values()" :key="note.id">
            <v-col cols="12">
              <NoteItem :note="note" @showEnlargedImage="showEnlargedImage" @showLikedUserList="showLikedUserList(note)"
                @showComments="showComments(note)">
                <template #actions>
                  <v-btn size="small" @click="showEditDialog(note)">
                    <v-icon size="large" icon="mdi-pencil-outline" />
                    <v-tooltip activator="parent" location="bottom" text="Edit" />
                  </v-btn>
                  <v-btn v-if="note.status.name === 'archived'" size="small" @click="showRetrieveConfirmDialog(note)">
                    <v-icon size="large" icon="mdi-keyboard-return" />
                    <v-tooltip activator="parent" location="bottom" text="Retrieve" />
                  </v-btn>
                  <v-btn v-else size="small" @click="showArchiveConfirmDialog(note)">
                    <v-icon size="large" icon="mdi-archive-plus-outline" />
                    <v-tooltip activator="parent" location="bottom" text="Archive" />
                  </v-btn>
                  <v-btn size="small" @click="showDeleteConfirmDialog(note)">
                    <v-icon size="large" icon="mdi-delete-outline" />
                    <v-tooltip activator="parent" location="bottom" text="Delete" />
                  </v-btn>
                </template>
              </NoteItem>
            </v-col>
          </template>
        </v-row>
      </v-infinite-scroll>
    </v-container>
  </AuthenticatedLayout>
  <v-dialog v-model="dialog.create" fullscreen scrollable>
    <NoteCreateForm @noteCreated="noteCreated" @close="dialog.create = false" />
  </v-dialog>
  <v-dialog v-model="dialog.edit" fullscreen scrollable>
    <NoteEditForm :targetNote @noteUpdated="noteUpdated" @close="dialog.edit = false" />
  </v-dialog>
  <v-dialog v-model="dialog.archiveConfirm" max-width="600">
    <ConfirmCard title="Archive Note" message="Are you sure you want to archive this note?"
      icon="mdi-archive-plus-outline" confirmBtnName="Archive" @confirmed="archiveNote"
      @close="dialog.archiveConfirm = false" />
  </v-dialog>
  <v-dialog v-model="dialog.retrieveConfirm" max-width="600">
    <ConfirmCard title="Retrieve" message="Are you sure you want to retrieve this note from the archive?"
      icon="mdi-keyboard-return" confirmBtnName="Retrieve" @confirmed="retrieveNote"
      @close="dialog.retrieveConfirm = false" />
  </v-dialog>
  <v-dialog v-model="dialog.deleteConfirm" max-width="600">
    <ConfirmCard icon="mdi-delete-outline" title="Delete Note" message="Are you sure you want to delete this note?"
      description="Once the note is deleted, it will be permanently deleted." confirmBtnName="Delete"
      confirmBtnColor="error" @confirmed="deleteNote" @close="dialog.deleteConfirm = false" />
  </v-dialog>
  <v-dialog v-model="dialog.searchText" max-width="600">
    <SearchTextForm :search @close="dialog.searchText = false" @apply="searchApply" />
  </v-dialog>
  <v-dialog v-model="dialog.sortMenu" max-width="600" scrollable>
    <NoteSortMenu :sort @close="dialog.sortMenu = false" @apply="sortApply" />
  </v-dialog>
  <v-dialog v-model="dialog.filterMenu" max-width="600" scrollable>
    <NoteFilterMenu :filter @close="dialog.filterMenu = false" @apply="filterApply" />
  </v-dialog>
  <v-dialog v-model="dialog.enlargedImage" close-on-content-click maxWidth="1000px">
    <v-img :src="previewImagePath" height="90vh" />
  </v-dialog>
  <v-dialog v-model="dialog.likedUserList" maxWidth="600px" scrollable>
    <LikedUserList :targetNote @close="dialog.likedUserList = false" />
  </v-dialog>
  <v-dialog v-model="dialog.noteComments" fullscreen scrollable transition="scroll-x-transition">
    <Comments :targetNote @close="dialog.noteComments = false">
      <NoteItem :note="targetNote" commentLinkDisabled @showEnlargedImage="showEnlargedImage"
        @showLikedUserList="showLikedUserList(targetNote)" />
    </Comments>
  </v-dialog>
</template>