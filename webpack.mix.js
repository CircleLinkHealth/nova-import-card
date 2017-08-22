let mix = require('laravel-mix');

mix.webpackConfig({
    devtool: "#cheap-module-source-map"
});

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

mix.sass('resources/assets/sass/css/provider/dashboard.scss', 'public/css/compiled/provider-dashboard.css');

mix.combine([
    'public/css/compiled/provider-dashboard.css',
    'resources/assets/less/css/animate.min.css'
], 'public/css/provider-dashboard.css');


/**
 *
 *
 * JS
 *
 */
mix.js('resources/assets/js/importer-training.js', 'public/js/compiled');

//apps
mix.js('resources/assets/js/app-provider-ui.js', 'public/js/compiled');
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/js/compiled').sourceMaps();
mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/js/compiled');


/*
 * The following is resources/assets/js/app-provider-ui.js broken up into separate parts, because Vue does not get along with webix and the previous UI
 */
mix.js('resources/assets/js/nurse-work-schedule.js', 'public/js/compiled');
mix.js('resources/assets/js/fab.js', 'public/js/compiled/v-fab.js');
mix.js('resources/assets/js/pdf-careplans.js', 'public/js/compiled/v-pdf-careplans.js');
mix.js('resources/assets/js/careplan-problems-list.js', 'public/js/compiled/v-careplan-problems-list.js');
mix.js('resources/assets/js/careplan-medications-list.js', 'public/js/compiled/v-careplan-medications-list.js');
mix.js('resources/assets/js/careplan-allergies-list.js', 'public/js/compiled/v-careplan-allergies-list.js');
mix.js('resources/assets/js/create-appointments-add-care-person.js', 'public/js/compiled/v-create-appointments-add-care-person.js');