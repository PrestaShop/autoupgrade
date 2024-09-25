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
        main: './src/ts/main.ts',
        theme: './src/scss/main.scss'
      },
      output: {
        dir: 'public'
      }
    }
  }
});
