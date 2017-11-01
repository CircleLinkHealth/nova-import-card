let mix = require('laravel-mix')

mix.webpackConfig({
    devtool: "#cheap-module-source-map"
})

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
], 'public/compiled/css/stylesheet.css')

mix.sass('resources/assets/sass/fab.scss', 'public/compiled/css')

mix.sass('resources/assets/sass/css/provider/dashboard.scss', 'public/compiled/css/provider-dashboard.css')

mix.combine([
    'public/compiled/css/provider-dashboard.css',
    'resources/assets/less/css/animate.min.css'
], 'public/compiled/css/provider-dashboard.css')


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
    'bower_components/jquery-idletimer/dist/idle-timer.js',
    'bower_components/select2/dist/js/select2.js',
    'bower_components/webix/codebase/webix.js',
    'bower_components/bootstrap/dist/js/bootstrap.js',
    'bower_components/bootstrap-select/dist/js/bootstrap-select.js',
    'public/js/typeahead.bundle.js',
    'public/js/DateTimePicker.min.js',
    'public/js/fab.js',   
], 'public/compiled/js/issue-688.js')
/** end fixing issue 688 */

/** start fixing admin-ui */
mix.combine([
    'bower_components/jquery/dist/jquery.js',
    'bower_components/jquery-ui/jquery-ui.js',
    'public/js/DateTimePicker.min.js',
    'bower_components/jquery-idletimer/dist/idle-timer.js',
    'public/js/jquery-ui-timepicker.min.js',
    'bower_components/parsleyjs/dist/parsley.js',
    'bower_components/bootstrap-select/dist/js/bootstrap-select.js',
    'bower_components/select2/dist/js/select2.js',
    'bower_components/bootstrap/dist/js/bootstrap.js'
], 'public/compiled/js/admin-ui.js')
/** end fixing admin-ui */

//apps
mix.js('resources/assets/js/app-provider-ui.js', 'public/compiled/js').sourceMaps()
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/compiled/js').sourceMaps()
mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/compiled/js').sourceMaps()
mix.js('resources/assets/js/admin/calls/main.js', 'public/compiled/js/v-call-mgmt.min.js').sourceMaps()