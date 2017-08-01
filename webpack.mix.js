let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/**
 *
 * CSS
 *
 */

// mix.less('resources/assets/less/css/wpstyle.less', 'public/css');

mix.combine([
    'resources/assets/less/css/app.less',
    'resources/assets/less/css/tooltip.less',
    'resources/assets/less/css/animate.min.css'
], 'public/css/stylesheet.css');

mix.sass('resources/assets/sass/fab.scss', 'public/css');

mix.sass('resources/assets/sass/css/provider/dashboard.scss', 'public/css/compiled/dashboard.css');

mix.combine([
    'public/css/compiled/dashboard.css',
    'resources/assets/less/css/animate.min.css'
], 'public/css/provider-dashboard.css');


/**
 *
 *
 * JS
 *
 */
mix.js('resources/assets/js/importer-training.js', 'public/js');

// mix.js('resources/assets/js/provider/create-locations.js', 'public/js');
// mix.js('resources/assets/js/provider/create-staff.js', 'public/js');


mix.js('resources/assets/js/app-provider-ui.js', 'public/js');
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/js');
mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/js');