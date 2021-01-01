const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();

mix.js('Resources/assets/js/app-provider-admin-panel-ui.js', 'public/compiled/js').sourceMaps();
mix.js('Resources/assets/js/app-clh-admin-ui.js', 'public/compiled/js').sourceMaps();

if (mix.inProduction()) {
    mix.version();
}