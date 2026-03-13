/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { defineConfig } from 'vite';
import { resolve } from 'path';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import del from 'rollup-plugin-delete';

function segmentIdsToChunks(id: string): string | null {
  const nodules = 'node_modules';

  if (id.includes(nodules)) {
    let parts = id.split(nodules);
    let modulePath = parts[1];
    // Remove the initial / if present
    if (modulePath.startsWith('/')) {
      modulePath = modulePath.slice(1);
    }

    // Split the rest by "/"
    // so you get @segment, analytics-core, dist, esm, analytics, dispatch.js
    let moduleParts = modulePath.split('/');

    // Create one chunk per Segment library
    if (moduleParts.length > 0 && moduleParts[0] === '@segment') {
      return `segment-${moduleParts[1]}`;
    }
  }

  // Keep original chunking
  return null;
}

export default defineConfig({
  base: './',
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler'
      }
    }
  },
  resolve: {
    alias: {
      '@fonts': resolve(__dirname, './src/fonts'),
      '@img': resolve(__dirname, './img')
    }
  },
  build: {
    assetsInlineLimit: 0,
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        app_ui_script: './src/ts/appUI/main.ts',
        app_update_notification_script: './src/ts/appUpdateNotification/main.ts',
        app_ui_theme: './src/scss/appUI/main.scss',
        app_update_notification_theme: './src/scss/appUpdateNotification/main.scss'
      },
      output: {
        dir: resolve(__dirname, '../views/'),
        entryFileNames: (chunkInfo) => {
          if (chunkInfo.name === 'app_ui_script') {
            return `js/autoupgrade.js`;
          } else if (chunkInfo.name === 'app_update_notification_script') {
            return 'js/autoupgrade-notification.js';
          }

          return 'js/[name].js';
        },
        manualChunks: segmentIdsToChunks,
        assetFileNames: (assetInfo) => {
          const assetName = assetInfo.name || '';

          if (assetName === 'app_ui_theme.css') {
            return 'css/autoupgrade.css';
          } else if (assetName === 'app_update_notification_theme.css') {
            return 'css/autoupgrade-notification.css';
          }

          if (/\.(webp|png|jpe?g|gif|svg)$/.test(assetName)) {
            return 'img/[name][extname]';
          }

          return 'assets/[name][extname]';
        }
      }
    },
    minify: 'terser',
    terserOptions: {
      format: {
        comments: false
      },
      mangle: {
        reserved: ['$']
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
