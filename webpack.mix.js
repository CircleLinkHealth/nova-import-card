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

mix.sass('resources/assets/sass/fab.scss', 'public/css');

mix.combine([
    '/css/provider/dashboard.scss',
    './resources/assets/less/css/animate.min.css'
], 'public/css/provider-dashboard.css');

// mix.js('resources/assets/js/app.js', 'public/js')
// ;
