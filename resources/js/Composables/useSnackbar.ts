import { ref } from 'vue';

export default function useSnackbar() {
  const snackbar = ref({
    display: false,
    message: '',
  });

  const showSnackBar = (msg: string): void => {
    snackbar.value.message = msg;
    snackbar.value.display = true;
  };

  return { snackbar, showSnackBar };
}
