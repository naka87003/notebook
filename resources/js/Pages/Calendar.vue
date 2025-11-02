<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, useTemplateRef } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { CalendarWeekdays } from 'vuetify/lib/composables/calendar.d.ts';
import { Note as NoteType } from '@/interfaces';
import EventNote from '@/Components/EventNote.vue';
import ConfirmCard from '@/Components/ConfirmCard.vue';
import NoteEditForm from '@/Components/NoteEditForm.vue';
import NoteCreateForm from '@/Components/NoteCreateForm.vue';
import useSnackbar from '@/Composables/useSnackbar';

const dialog = ref({
  eventNote: false,
  create: false,
  edit: false,
  archiveConfirm: false,
  retrieveConfirm: false,
  deleteConfirm: false,
});

const calendar = useTemplateRef('calendar');

const type = ref<'month' | 'day' | 'week' | '4day'>('month');
const schedule = ref<NoteType[]>([]);
const targetNote = ref<NoteType>();

const weekdays = ref<CalendarWeekdays[]>([0, 1, 2, 3, 4, 5, 6]);

const focus = ref();

const events = ref([]);

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
          name: item.title,
          start: new Date(item.starts_at),
          end: new Date(item.ends_at),
          color: item.tag?.hex_color,
          timed: true,
        };
      });
    })
    .catch((error) => {
      console.log(error);
    });
};

const showEvent = (nativeEvent, { event }): void => {
  targetNote.value = schedule.value.find((item) => item.id === event.id);
  dialog.value.eventNote = true;
};

// Snackbar
const { snackbar, showSnackBar } = useSnackbar();
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

const viewDay = (nativeEvent, { date }) => {
  focus.value = date;
  type.value = 'day';
};

const setToday = () => {
  focus.value = '';
};

onMounted(async () => {
  getSchedule();
});
</script>

<template>
  <Head title="Calendar" />
  <v-snackbar v-model="snackbar.display" location="top right" color="success" timeout="3000">
    <v-icon class="me-3" style="margin-bottom: 2px">mdi-check-circle</v-icon>{{ snackbar.message }}
  </v-snackbar>
  <AuthenticatedLayout>
    <template #action>
      <v-btn class="me-4" variant="tonal" @click="setToday"> Today </v-btn>
      <v-menu location="bottom end">
        <template #activator="{ props }">
          <v-btn variant="tonal" v-bind="props">
            <span>{{ type }}</span>
            <v-icon end> mdi-menu-down </v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item @click="type = 'day'">
            <v-list-item-title>Day</v-list-item-title>
          </v-list-item>
          <v-list-item @click="type = 'week'">
            <v-list-item-title>Week</v-list-item-title>
          </v-list-item>
          <v-list-item @click="type = 'month'">
            <v-list-item-title>Month</v-list-item-title>
          </v-list-item>
          <v-list-item @click="type = '4day'">
            <v-list-item-title>4 days</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
      <v-spacer />
      <v-btn @click="dialog.create = true">
        <v-icon size="x-large" icon="mdi-plus" />
        <v-tooltip activator="parent" location="bottom" text="New" />
      </v-btn>
    </template>
    <v-container>
      <v-sheet height="64">
        <v-toolbar flat>
          <v-btn size="small" variant="text" icon @click="calendar?.prev()">
            <v-icon size="small"> mdi-chevron-left </v-icon>
          </v-btn>
          <v-btn size="small" variant="text" icon @click="calendar?.next()">
            <v-icon size="small"> mdi-chevron-right </v-icon>
          </v-btn>
          <v-toolbar-title> {{ calendar?.title }} </v-toolbar-title>
        </v-toolbar>
      </v-sheet>
      <v-sheet height="600">
        <v-calendar
          ref="calendar"
          v-model="focus"
          :events
          :type
          :weekdays
          color="primary"
          @click:date="viewDay"
          @click:event="showEvent"
        />
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
