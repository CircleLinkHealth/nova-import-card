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
mix.combine([
    'resources/assets/less/css/app.less',
    'resources/assets/less/css/tooltip.less',
    'resources/assets/less/css/animate.min.css'
], 'public/compiled/css/stylesheet.css');

mix.sass('resources/assets/sass/fab.scss', 'public/compiled/css');

mix.sass('resources/assets/sass/css/provider/dashboard.scss', 'public/compiled/css/provider-dashboard.css');

mix.combine([
    'public/compiled/css/provider-dashboard.css',
    'resources/assets/less/css/animate.min.css'
], 'public/compiled/css/provider-dashboard.css');


/**
 *
 *
 * JS
 *
 */

/** start fixing issue 688 */
mix.combine([
    'bower_components/jquery/dist/jquery.js',
    'bower_components/jquery-ui/jquery-ui.js',
    'bower_components/select2/dist/select2.js',
    'bower_components/webix/codebase/webix.js',
    'bower_components/jquery-idletimer/dist/idle-timer.js',
    'bower_components/bootstrap-select/dist/js/bootstrap-select.js',
    'bower_components/bootstrap/dist/js/bootstrap.js',
    'public/js/typeahead.bundle.js',
    'public/js/DateTimePicker.min.js',
    'public/js/fab.js',   
], 'public/compiled/js/issue-688.js');

mix.js('public/compiled/js/issue-688.js', 'public/compiled/js');
// mix.combine([
//     'node_modules/jquery/dist/jquery.js',
//     ''
// ], 'public/compiled/js')
/** end fixing issue 688 */

mix.js('resources/assets/js/importer-training.js', 'public/compiled/js').sourceMaps();

//apps
mix.js('resources/assets/js/app-provider-ui.js', 'public/compiled/js');
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/compiled/js');


/*
 * The following is resources/assets/js/app-provider-ui.js broken up into separate parts, because Vue does not get along with webix and the previous UI
 */
mix.js('resources/assets/js/nurse-work-schedule.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/fab.js', 'public/compiled/js/v-fab.js');
mix.js('resources/assets/js/pdf-careplans.js', 'public/compiled/js/v-pdf-careplans.js');
mix.js('resources/assets/js/careplan-problems-list.js', 'public/compiled/js/v-careplan-problems-list.js');
mix.js('resources/assets/js/careplan-medications-list.js', 'public/compiled/js/v-careplan-medications-list.js');
mix.js('resources/assets/js/careplan-allergies-list.js', 'public/compiled/js/v-careplan-allergies-list.js');
mix.js('resources/assets/js/create-appointments-add-care-person.js', 'public/compiled/js/v-create-appointments-add-care-person.js');