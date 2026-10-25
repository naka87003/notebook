import eslint from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import eslintPluginVue from 'eslint-plugin-vue';
import vuetify from 'eslint-plugin-vuetify';
import globals from 'globals';
import typescriptEslint from 'typescript-eslint';
import { defineConfig } from 'eslint/config';

export default defineConfig(
  { ignores: ['*.d.ts', '**/coverage', '**/dist', 'vendor/**'] },
  ...vuetify.configs['flat/recommended-v4'],
  {
    extends: [
      eslint.configs.recommended,
      ...typescriptEslint.configs.recommended,
      ...eslintPluginVue.configs['flat/recommended'],
    ],
    files: ['**/*.{ts,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: globals.browser,
      parserOptions: {
        parser: typescriptEslint.parser,
      },
    },
    rules: {
      'vue/multi-word-component-names': 'off',
      'vue/no-template-shadow': 'off',
      'vue/require-default-prop': 'off',
      'vue/valid-v-slot': 'off',
      'no-undef': 'off',
      '@typescript-eslint/consistent-type-imports': [
        'error',
        {
          prefer: 'type-imports',
          fixStyle: 'separate-type-imports',
        },
      ],
    },
  },
  eslintConfigPrettier
);
