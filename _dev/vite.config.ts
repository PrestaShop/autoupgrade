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
import pkg from './package.json' assert { type: 'json' };

export default defineConfig({
  base: './',
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler'
      }
    }
  },
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
            const moduleVersion = pkg.version;
            return `js/autoupgrade.v${moduleVersion}.js`;
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
