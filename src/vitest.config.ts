import { defineConfig } from 'vitest/config';

process.env.TZ = 'Asia/Tokyo';

export default defineConfig({
  test: {
    globals: true,
    environment: 'jsdom',
  },
});
