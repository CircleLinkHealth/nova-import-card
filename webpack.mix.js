const mix = require('laravel-mix');

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

const devTool = process.env.NODE_ENV === 'production' ? 'source-map' : 'eval-source-map';
mix.webpackConfig({
    devtool: devTool
});

mix.js('resources/js/app.js', 'public/js/compiled')
    .sass('resources/sass/app.scss', 'public/css');
