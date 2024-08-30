// @ts-check

import eslint from '@eslint/js';
import tseslintPlugin from '@typescript-eslint/eslint-plugin';
import tseslintParser from '@typescript-eslint/parser';
import eslintPluginPrettier from 'eslint-plugin-prettier';
import globals from 'globals';

export default [
  // Inclure la configuration recommandée d'ESLint
  eslint.configs.recommended,

  // Inclure la configuration recommandée pour TypeScript
  {
    files: ['**/*.ts', '**/*.tsx'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node
      },
      parser: tseslintParser,
      parserOptions: {
        project: './tsconfig.json'
      }
    },
    plugins: {
      '@typescript-eslint': tseslintPlugin
    },
    rules: {
      ...tseslintPlugin.configs.recommended.rules
    }
  },

  // Inclure la configuration recommandée pour Prettier
  {
    plugins: {
      prettier: eslintPluginPrettier
    },
    rules: {
      ...eslintPluginPrettier.configs.recommended.rules
    }
  }
];
