<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type {
  EmailPreference,
  EmailPreferenceType,
  EmailPreferenceValue,
} from '@/Types/interfaces';

type EmailPreferencesForm = Record<EmailPreferenceType, EmailPreferenceValue>;

const props = defineProps<{
  emailPreferences: EmailPreference[];
}>();

const form = useForm<{ emailPreferences: EmailPreferencesForm }>({
  emailPreferences: props.emailPreferences.reduce<EmailPreferencesForm>(
    (preferences, { type, value }) => {
      preferences[type] = value;
      return preferences;
    },
    {
      like: 0,
      follow: 0,
      comment: 0,
      reply: 0,
    }
  ),
});
</script>

<template>
  <Head title="Preferences" />
  <AuthenticatedLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <section>
            <v-card title="Email Preferences" prepend-icon="mdi-email-outline">
              <v-divider />
              <v-card-text>
                <v-card color="surface-variant" variant="tonal">
                  <v-card-text class="text-medium-emphasis text-body-small">
                    Update your preferences.
                  </v-card-text>
                </v-card>
                <form @submit.prevent="form.patch(route('preferences.update'))">
                  <template v-for="item in emailPreferences" :key="item.id">
                    <v-checkbox
                      v-model="form.emailPreferences[item.type]"
                      class="mb-n10"
                      :true-value="1"
                      :false-value="0"
                    >
                      <template #label>
                        {{ item.type }}
                      </template>
                    </v-checkbox>
                  </template>
                </form>
                <Transition>
                  <div v-if="form.recentlySuccessful" class="mt-5">
                    <v-alert text="Saved." type="success" variant="tonal" closable />
                  </div>
                </Transition>
              </v-card-text>
              <v-divider />
              <template #actions>
                <v-spacer />
                <v-btn
                  color="primary"
                  size="large"
                  variant="tonal"
                  :class="{
                    'text-disabled': form.processing,
                  }"
                  :disabled="form.processing"
                  @click="
                    form.patch(route('preferences.update'), {
                      preserveScroll: true,
                    })
                  "
                >
                  Save
                </v-btn>
              </template>
            </v-card>
          </section>
        </v-col>
      </v-row>
    </v-container>
  </AuthenticatedLayout>
</template>
