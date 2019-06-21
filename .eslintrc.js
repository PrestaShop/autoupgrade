module.exports = {
  root: true,
  env: {
    node: true
  },
  'extends': [
    'eslint-config-prestashop',
    'plugin:vue/strongly-recommended',
  ],
  plugins: [
    'import',
    'vue'
  ],
  rules: {
    'vue/script-indent': [
      'error',
      2,
      {
        'baseIndent': 1
      }
    ],
    'vue/singleline-html-element-content-newline': 'off',
    'vue/max-attributes-per-line': 'off',
    'no-console': 'off',
    'no-debugger': 'off'
  },
  'overrides': [
    {
      'files': ['*.vue'],
      'rules': {
        'indent': 'off'
      }
    }
  ],
}
