import { defineConfig } from 'vite';
import { resolve } from 'path';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import del from 'rollup-plugin-delete';

export default defineConfig({
  base: './',
  build: {
    assetsInlineLimit: 0,
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        main: './src/ts/main.ts',
        theme: './src/scss/main.scss'
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
          const assetName = assetInfo.name || '';

          if (assetName.endsWith('.css')) {
            return 'css/autoupgrade.css';
          } else if (/\.(webp|png|jpe?g|gif|svg)$/.test(assetName)) {
            return 'img/[name].[ext]';
          }
          return 'assets/[name].[ext]';
        }
      }
    }
  },
  plugins: [
    del({
      targets: [
        resolve(__dirname, '../views/js/*'),
        resolve(__dirname, '../views/css/*'),
        resolve(__dirname, '../views/img/*'),
        resolve(__dirname, '../views/assets/*')
      ],
      force: true,
      verbose: true,
      hook: 'buildStart'
    }),
    viteStaticCopy({
      targets: [
        {
          src: './img/*',
          dest: resolve(__dirname, '../views/img/')
        }
      ]
    })
  ]
});
