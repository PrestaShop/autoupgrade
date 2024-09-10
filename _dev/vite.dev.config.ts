import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    fs: {
      strict: false
    }
  },
  build: {
    rollupOptions: {
      input: {
        main: './scripts/main.ts',
        theme: './styles/main.scss'
      },
      output: {
        dir: 'public'
      }
    }
  }
});
