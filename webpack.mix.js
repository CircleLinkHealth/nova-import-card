let mix = require('laravel-mix')
const path = require('path')
const WorkboxPlugin = require('workbox-webpack-plugin')
const DIST_DIR = 'public'
const SRC_DIR = 'resources/assets'

const webpackConfig = {
    devtool: "#source-map",
    node: {
        fs: 'empty' //to help webpack resolve 'fs'
    },
    externals: [
        {
            './cptable': 'var cptable'
        }
    ],
    plugins: [
        new WorkboxPlugin({
            globDirectory: DIST_DIR,
            globPatterns: ['chunk-*.js', 'compiled/**/!(sw|workbox)*.{js,css}', 'css/app.css', 'css/admin.css', 'css/wpstyle.css'],
            swDest: path.join(DIST_DIR, 'sw.js'),
            swSrc: path.join(SRC_DIR, 'js/sw.js')
        }),
    ]
}

mix.webpackConfig(webpackConfig)

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
mix.less('resources/assets/less/css/app.less', 'public/compiled/css/app-compiled.css')

mix.combine([
    'public/compiled/css/app-compiled.css',
    'resources/assets/less/css/animate.min.css'
], 'public/compiled/css/stylesheet.css')

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
mix.js('resources/assets/js/app.js', 'public/compiled/js').sourceMaps()
mix.js('resources/assets/js/app-provider-ui.js', 'public/compiled/js').sourceMaps()
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/compiled/js').sourceMaps()

if (mix.inProduction) {
    mix.options({
        uglify: false,
      })
}

mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/compiled/js').sourceMaps()
mix.js('resources/assets/js/app-ccd-importer.js', 'public/compiled/js').sourceMaps()