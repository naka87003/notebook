import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { imageUrl } from '@/Utils/common';

export function useCurrentUser() {
  const page = usePage();
  const user = computed(() => page.props.auth.user);
  const userImagePath = computed(() => user.value?.image_path ?? null);
  const avatarImagePath = computed(() => imageUrl(userImagePath.value));
  const userName = computed(() => user.value?.name ?? '');
  const userEmail = computed(() => user.value?.email ?? '');

  return { user, userImagePath, avatarImagePath, userName, userEmail };
}
