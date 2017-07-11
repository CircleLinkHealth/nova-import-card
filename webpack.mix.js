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
    '/css/app.less',
    '/css/animate.min.css'
], 'public/css/stylesheet.css');

mix.sass('resources/assets/sass/fab.scss', 'public/css');

mix.combine([
    '/css/provider/dashboard.scss',
    './resources/assets/less/css/animate.min.css'
], 'public/css/provider-dashboard.css');


/**
 *
 *
 * JS
 *
 */
mix.js('resources/assets/js/importer-training.js', 'public/js');