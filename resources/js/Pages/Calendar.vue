<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { type Ref, ref, onMounted } from 'vue';
import type { CalendarWeekdays } from 'vuetify/lib/composables/calendar.d.ts';
import { Note as NoteType } from '@/interfaces';
import EventNote from '@/Components/EventNote.vue';
import ConfirmCard from '@/Components/ConfirmCard.vue';
import NoteEditForm from '@/Components/NoteEditForm.vue';
import NoteCreateForm from '@/Components/NoteCreateForm.vue';

const dialog = ref({
  eventNote: false,
  create: false,
  edit: false,
  archiveConfirm: false,
  retrieveConfirm: false,
  deleteConfirm: false,
});

const snackbar = ref({
  display: false,
  message: '',
});

const type: Ref<'month' | 'day' | 'week'> = ref('month');

const schedule: Ref<NoteType[]> = ref([]);
const targetNote: Ref<NoteType> = ref();

const items = {
  types: [
    { value: 'month', mdi_name: 'mdi-calendar-month-outline' },
    { value: 'week', mdi_name: 'mdi-calendar-week-outline' },
    { value: 'day', mdi_name: 'mdi-calendar-today-outline' },
  ],
};

const weekdays = ref<CalendarWeekdays[]>([0, 1, 2, 3, 4, 5, 6]);

const value = ref();

const events = ref([]);

onMounted(async () => {
  getSchedule();
});

const getSchedule = async () => {
  await axios
    .get(route('notes.schedule'), {
      params: {},
    })
    .then((response) => {
      schedule.value = response.data;
      events.value = response.data.map((item: NoteType) => {
        return {
          id: item.id,
          title: item.title,
          start: new Date(item.starts_at),
          end: new Date(item.ends_at),
          color: item.tag?.hex_color,
        };
      });
    })
    .catch((error) => {
      console.log(error);
    });
};

const showSnackBar = (msg: string): void => {
  snackbar.value.message = msg;
  snackbar.value.display = true;
};

const showEvent = (id): void => {
  targetNote.value = schedule.value.find((item) => item.id === id);
  dialog.value.eventNote = true;
};

const noteCreated = async () => {
  dialog.value.create = false;
  await getSchedule();
  dialog.value.eventNote = false;
  showSnackBar('Updated Successfully.');
};

const noteUpdated = async () => {
  dialog.value.edit = false;
  await getSchedule();
  dialog.value.eventNote = false;
  showSnackBar('Updated Successfully.');
};

const showEditDialog = (item: NoteType): void => {
  targetNote.value = item;
  dialog.value.edit = true;
};

const showDeleteConfirmDialog = (item: NoteType): void => {
  targetNote.value = item;
  dialog.value.deleteConfirm = true;
};

const deleteNote = async (): Promise<void> => {
  dialog.value.deleteConfirm = false;
  await axios
    .delete(route('notes.destroy', targetNote.value.id))
    .then(async () => {
      await getSchedule();
      dialog.value.eventNote = false;
      showSnackBar('Deleted Successfully.');
    })
    .catch((error) => {
      console.log(error);
    });
};
</script>

<template>
  <Head title="Calendar" />
  <v-snackbar v-model="snackbar.display" location="top right" color="success" timeout="3000">
    <v-icon class="me-3" style="margin-bottom: 2px">mdi-check-circle</v-icon>{{ snackbar.message }}
  </v-snackbar>
  <AuthenticatedLayout>
    <template #action>
      <v-select
        v-model="type"
        :items="items.types"
        density="compact"
        variant="solo-filled"
        flat
        hide-details
      >
        <template #item="{ props, item }">
          <v-list-item v-bind="props" :title="item.raw.value" density="compact">
            <template #prepend>
              <v-icon :icon="item.raw.mdi_name" />
            </template>
          </v-list-item>
        </template>
        <template #selection="{ item }">
          <v-list-item :title="item.raw.value" density="compact">
            <template #prepend>
              <v-icon :icon="item.raw.mdi_name" />
            </template>
          </v-list-item>
        </template>
      </v-select>
      <v-spacer></v-spacer>
      <v-btn @click="dialog.create = true">
        <v-icon size="x-large" icon="mdi-plus" />
        <v-tooltip activator="parent" location="bottom" text="New" />
      </v-btn>
    </template>
    <v-container>
      <v-sheet class="overflow-auto">
        <v-calendar ref="calendar" v-model="value" :events :view-mode="type" :weekdays>
          <template #day-event="{ event }">
            <v-btn
              size="small"
              variant="tonal"
              rounded="0"
              class="hidden-md-and-down"
              @click="showEvent(event.id)"
            >
              <template #prepend>
                <v-icon size="x-small" icon="mdi-circle" :color="String(event.color)"></v-icon>
              </template>
              <span class="text-truncate" style="max-width: 120px">{{ event.title }}</span>
            </v-btn>
            <div class="text-center hidden-lg-and-up">
              <v-icon
                size="x-small"
                icon="mdi-circle"
                :color="String(event.color)"
                @click="showEvent(event.id)"
              />
            </div>
          </template>
        </v-calendar>
      </v-sheet>
    </v-container>
    <v-dialog v-model="dialog.eventNote" max-width="1000">
      <EventNote :target-note @close="dialog.eventNote = false">
        <template #actions="{ targetNote }">
          <v-btn size="small" @click="showEditDialog(targetNote)">
            <v-icon size="large" icon="mdi-pencil-outline" />
            <v-tooltip activator="parent" location="bottom" text="Edit" />
          </v-btn>
          <v-btn size="small" @click="showDeleteConfirmDialog(targetNote)">
            <v-icon size="large" icon="mdi-delete-outline" />
            <v-tooltip activator="parent" location="bottom" text="Delete" />
          </v-btn>
        </template>
      </EventNote>
    </v-dialog>
    <v-dialog v-model="dialog.create" fullscreen scrollable>
      <NoteCreateForm variant="event" @note-created="noteCreated" @close="dialog.create = false" />
    </v-dialog>
    <v-dialog v-model="dialog.edit" fullscreen scrollable>
      <NoteEditForm
        :target-note
        variant="event"
        @note-updated="noteUpdated"
        @close="dialog.edit = false"
      />
    </v-dialog>
    <v-dialog v-model="dialog.deleteConfirm" max-width="600">
      <ConfirmCard
        icon="mdi-delete-outline"
        title="Delete Event"
        message="Are you sure you want to delete this event?"
        description="Once the event is deleted, it will be permanently deleted."
        confirm-btn-name="Delete"
        confirm-btn-color="error"
        @confirmed="deleteNote"
        @close="dialog.deleteConfirm = false"
      />
    </v-dialog>
  </AuthenticatedLayout>
</template>
