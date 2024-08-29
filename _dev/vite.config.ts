import { defineConfig } from 'vite';
import { resolve } from 'path';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
  // define global variable like process.env.MY_VAR
  define: {},
  plugins: [symfonyPlugin()],
  build: {
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        main: './scripts/main.ts',
        theme: './styles/main.scss'
      },
      output: {
        validate: true,
        dir: resolve(__dirname, '../views/'),
        entryFileNames: (chunkInfo) => {
          if (
            chunkInfo.facadeModuleId?.endsWith('.ts') ||
            chunkInfo.facadeModuleId?.endsWith('.js')
          ) {
            return 'js/autoupgrade.js';
          }
          return 'js/[name].js';
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'css/autoupgrade.css';
          }
          return 'css/[name].[ext]';
        },
        // set global variable to let it then building like $ of jQuery
        globals: {}
      }
    }
  }
});
