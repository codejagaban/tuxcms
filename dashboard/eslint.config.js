import js from '@eslint/js'
import globals from 'globals'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import { defineConfig, globalIgnores } from 'eslint/config'

export default defineConfig([
  globalIgnores(['dist']),
  {
    files: ['**/*.{js,jsx}'],
    extends: [
      js.configs.recommended,
      reactHooks.configs.flat.recommended,
      reactRefresh.configs.vite,
    ],
    languageOptions: {
      ecmaVersion: 2020,
      globals: globals.browser,
      parserOptions: {
        ecmaVersion: 'latest',
        ecmaFeatures: { jsx: true },
        sourceType: 'module',
      },
    },
    rules: {
      'no-unused-vars': ['error', {
        varsIgnorePattern: '^[A-Z_]',
        argsIgnorePattern: '^[A-Z_]',
      }],
    },
  },
  {
    files: ['src/lib/auth.jsx'],
    rules: {
      'react-refresh/only-export-components': 'off',
    },
  },
  {
    // Every icon is built by `icon()` or `drawn()`, factories the linter cannot
    // see through, so it reads each export as a plain value. Name the two
    // factories instead of switching the rule off for the file.
    files: ['src/lib/icons.jsx'],
    rules: {
      'react-refresh/only-export-components': ['error', {
        allowConstantExport: true,
        extraHOCs: ['icon', 'drawn'],
      }],
    },
  },
])
