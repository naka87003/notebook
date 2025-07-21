import { watch } from 'vue';
import { useTheme } from 'vuetify';
import { useDark } from '@vueuse/core';

export function useDarkMode() {
  const theme = useTheme();
  const isDark = useDark();

  watch(
    isDark,
    (value) => {
      theme.change(value ? 'dark' : 'light');
    },
    { immediate: true }
  );
  return { isDark };
}
