import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

process.env.TZ = 'Asia/Tokyo';

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
  },
});
