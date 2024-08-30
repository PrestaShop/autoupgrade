import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  base: './',
  build: {
    assetsInlineLimit: 0,
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        main: './scripts/main.ts',
        theme: './styles/main.scss'
      },
      output: {
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
          } else if (/\.(webp|png|jpe?g|gif|svg)$/.test(assetInfo.name)) {
            return 'img/[name].[ext]';
          }
          return 'assets/[name].[ext]';
        }
      }
    }
  }
});
