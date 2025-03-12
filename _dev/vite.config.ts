/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
        app_ui_theme: './src/scss/appUI/main.scss'
      },
      output: {
        dir: resolve(__dirname, '../views/'),
        entryFileNames: (chunkInfo) => {
          if (chunkInfo.name === 'app_ui_script') {
            return `js/autoupgrade.js`;
          }
          return 'js/[name].js';
        },
        manualChunks: segmentIdsToChunks,
        assetFileNames: (assetInfo) => {
          const assetName = assetInfo.name || '';

          if (assetName === 'app_ui_theme.css') {
            return 'css/autoupgrade.css';
          } else if (/\.(webp|png|jpe?g|gif|svg)$/.test(assetName)) {
            return 'img/[name].[extname]';
          }
          return 'assets/[name].[extname]';
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
