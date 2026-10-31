<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import TagCreateForm from './TagCreateForm.vue';
import type { Category } from '@/Types/interfaces';
import useTagOptions from '@/Composables/useTagOptions';

dayjs.extend(utc);

const props = defineProps<{
  variant?: string;
}>();

const emit = defineEmits<{
  close: [];
  noteCreated: [];
}>();

const page = usePage<{ categoryItems: Category[] }>();
const form = useForm<{
  title: string;
  content: string;
  category: number;
  public: number;
  tag: number | null;
  starts: string;
  ends: string;
  image: File | null;
}>({
  title: '',
  content: '',
  category: 1,
  public: 1,
  tag: null,
  starts: dayjs().add(1, 'hour').utc().format(),
  ends: dayjs().add(2, 'hour').utc().format(),
  image: null,
});

const preview = ref();

const dialog = ref({
  tagCreate: false,
});

const allDay = ref(false);

const categoryOptions = computed(() => page.props.categoryItems);

const eventMode = computed((): boolean => props.variant === 'event');

const startDateTime = computed({
  get() {
    if (allDay.value) {
      return dayjs(form.starts).format('YYYY-MM-DD');
    } else {
      return dayjs(form.starts).format('YYYY-MM-DD HH:mm');
    }
  },
  set(newValue) {
    if (allDay.value) {
      form.starts = dayjs(newValue).startOf('day').utc().format();
    } else {
      form.starts = dayjs(newValue).utc().format();
    }
  },
});

const endDateTime = computed({
  get() {
    if (allDay.value) {
      return dayjs(form.ends).format('YYYY-MM-DD');
    } else {
      return dayjs(form.ends).format('YYYY-MM-DD HH:mm');
    }
  },
  set(newValue) {
    if (allDay.value) {
      form.ends = dayjs(newValue).endOf('day').utc().format();
    } else {
      form.ends = dayjs(newValue).utc().format();
    }
  },
});

const previewImagePath = computed(() => {
  if (form.image) {
    return URL.createObjectURL(preview.value.files[0]);
  } else {
    return null;
  }
});

onMounted(async () => {
  if (eventMode.value) {
    form.category = 3;
  }
});

const { state: tagItems, execute } = useTagOptions();

const toAllDayRange = () => {
  form.starts = dayjs(form.starts).startOf('day').utc().format();
  form.ends = dayjs(form.ends).endOf('day').utc().format();
};

const tagCreated = async (id: number) => {
  await execute();
  form.tag = id;
  dialog.value.tagCreate = false;
};

const submit = () => {
  form.post(route('notes.store'), {
    onSuccess: () => {
      emit('noteCreated');
    },
  });
};

const copyDateToEnd = () => {
  if (form.starts >= form.ends) {
    if (allDay.value) {
      form.ends = dayjs(form.starts).endOf('day').utc().format();
    } else {
      form.ends = dayjs(form.starts).add(1, 'hour').utc().format();
    }
  }
};
</script>
<template>
  <v-card>
    <v-toolbar density="comfortable" color="transparent">
      <v-toolbar-title class="text-title-large">
        <template v-if="eventMode">Create New Event</template>
        <template v-else>Create New Note</template>
      </v-toolbar-title>
      <template #prepend>
        <v-icon class="ms-3" icon="mdi-plus" />
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
      <form enctype="multipart/form-data" @submit.prevent="submit">
        <v-row>
          <v-col cols="12" md="6">
            <v-row>
              <v-col cols="12">
                <div class="text-body-large text-medium-emphasis">Title</div>
                <v-text-field
                  v-model="form.title"
                  hide-details="auto"
                  type="text"
                  density="compact"
                  placeholder="Enter Title"
                  variant="outlined"
                  :error="Boolean(form.errors.title)"
                  :error-messages="form.errors.title"
                  required
                  autofocus
                  maxlength="50"
                  @update:model-value="form.clearErrors('title')"
                />
              </v-col>
              <v-col cols="12">
                <div class="text-body-large text-medium-emphasis">Content</div>
                <v-textarea
                  v-model="form.content"
                  hide-details="auto"
                  type="text"
                  density="compact"
                  placeholder="Enter Content"
                  variant="outlined"
                  :error="Boolean(form.errors.content)"
                  :error-messages="form.errors.content"
                  required
                  auto-grow
                  :counter="page.props.noteContentMaxLength"
                  :maxlength="page.props.noteContentMaxLength"
                  @update:model-value="form.clearErrors('content')"
                />
                <div class="text-body-small text-medium-emphasis mt-1">
                  For code, add ``` on separate lines before and after it. Add a language after the
                  opening ```, such as ```js, for syntax highlighting.
                </div>
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="12" md="6">
            <v-row>
              <v-col cols="12">
                <v-img v-if="previewImagePath" :src="previewImagePath" width="300" />
                <div class="text-body-large text-medium-emphasis">Image Upload</div>
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
                  @update:model-value="form.clearErrors('image')"
                />
              </v-col>
              <v-col v-if="!eventMode" cols="12">
                <div class="text-body-large text-medium-emphasis">Category</div>
                <v-autocomplete
                  v-model="form.category"
                  hide-details="auto"
                  :items="categoryOptions"
                  density="compact"
                  placeholder="Select Category"
                  variant="outlined"
                  :error="Boolean(form.errors.category)"
                  :error-messages="form.errors.category"
                  required
                  item-title="name"
                  item-value="id"
                  @update:model-value="form.clearErrors('category')"
                >
                  <template #item="{ props, item }">
                    <v-list-item v-bind="props" prepend-icon="mdi-tag" :title="item.name">
                      <template #prepend>
                        <v-icon :icon="item.mdi_name" />
                      </template>
                    </v-list-item>
                  </template>
                  <template #selection="{ item }">
                    <v-list-item prepend-icon="mdi-tag" :title="item.name">
                      <template #prepend>
                        <v-icon :icon="item.mdi_name" />
                      </template>
                    </v-list-item>
                  </template>
                </v-autocomplete>
              </v-col>
              <v-col v-if="form.category === 3" cols="12" lg="6">
                <div class="text-body-large text-medium-emphasis">DateTime</div>
                <v-switch
                  v-model="allDay"
                  color="primary"
                  density="compact"
                  label="All-day"
                  hide-details
                  inset
                  @update:model-value="toAllDayRange"
                ></v-switch>
                <v-text-field
                  v-model="startDateTime"
                  class="mt-3"
                  label="Starts"
                  hide-details="auto"
                  :type="allDay ? 'date' : 'datetime-local'"
                  density="compact"
                  variant="outlined"
                  :error="Boolean(form.errors.starts)"
                  :error-messages="form.errors.starts"
                  required
                  @update:model-value="
                    form.clearErrors('starts');
                    copyDateToEnd();
                  "
                />
                <v-text-field
                  v-model="endDateTime"
                  class="mt-3"
                  label="Ends"
                  hide-details="auto"
                  :type="allDay ? 'date' : 'datetime-local'"
                  density="compact"
                  variant="outlined"
                  :error="Boolean(form.errors.ends)"
                  :error-messages="form.errors.ends"
                  required
                  @update:model-value="form.clearErrors('ends')"
                />
              </v-col>
              <v-col cols="12">
                <div class="text-body-large text-medium-emphasis">Tag</div>
                <v-autocomplete
                  v-model="form.tag"
                  hide-details="auto"
                  :items="tagItems"
                  density="compact"
                  placeholder="Select Tag"
                  variant="outlined"
                  :error="Boolean(form.errors.tag)"
                  append-icon="mdi-tag-plus-outline"
                  :error-messages="form.errors.tag"
                  required
                  item-title="name"
                  item-value="id"
                  clearable
                  @update:model-value="form.clearErrors('tag')"
                  @click:append="dialog.tagCreate = true"
                >
                  <template #item="{ props, item }">
                    <v-list-item v-bind="props" :title="item.name">
                      <template #prepend>
                        <v-icon icon="mdi-tag" :color="item.hex_color" />
                      </template>
                    </v-list-item>
                  </template>
                  <template #selection="{ item }">
                    <v-list-item :title="item.name">
                      <template #prepend>
                        <v-icon icon="mdi-tag" :color="item.hex_color" />
                      </template>
                    </v-list-item>
                  </template>
                </v-autocomplete>
              </v-col>
              <v-col cols="12">
                <div class="text-body-large text-medium-emphasis">Visibility</div>
                <v-radio-group
                  v-model="form.public"
                  :error-messages="form.errors.public"
                  :error="Boolean(form.errors.public)"
                  hide-details="auto"
                >
                  <v-radio label="Public" :value="1" />
                  <v-radio label="Private" :value="0" />
                </v-radio-group>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </form>
    </v-card-text>
    <v-divider />
    <template #actions>
      <v-spacer />
      <v-btn variant="plain" @click="$emit('close')">Close</v-btn>
      <v-btn color="primary" variant="tonal" :disabled="form.processing" @click="submit"
        >Save</v-btn
      >
    </template>
  </v-card>
  <v-dialog v-model="dialog.tagCreate" max-width="600">
    <TagCreateForm @close="dialog.tagCreate = false" @tag-created="tagCreated" />
  </v-dialog>
</template>
