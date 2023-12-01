let mix = require('laravel-mix')

require('./nova.mix')

mix
  .setPublicPath('dist')
  .js('resources/js/multiselect-field.js', 'js')
  .vue({ version: 3 })
  .nova('{{ name }}')
