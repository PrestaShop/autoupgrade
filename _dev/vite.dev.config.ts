import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    // Configure la racine pour servir les fichiers à partir du bon répertoire
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
        // Assurez-vous que les fichiers sont sortis dans le bon répertoire
        dir: 'public'
      }
    }
  }
});
