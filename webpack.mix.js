let mix = require('laravel-mix');

//
// NOTE:
//
// We use webpack in combination with Laravel Mix.
//
// We use chunks from webpack (see app-clh-admin-ui.js)
// For the chunk we use webpack output property to define a filename with a hash for them.
//
// For the rest of the files, we use Laravel Mix
// We use Mix's version() method + mix() in blade.php files to add version on the url.
//

const webpackConfig = {
    devtool: "#source-map",
    output: {
        publicPath: "/",
        chunkFilename: '[name].[chunkhash].js'
    },
    node: {
        fs: 'empty' //to help webpack resolve 'fs'
    },
    externals: [
        {
            './cptable': 'var cptable'
        }
    ],
    plugins: [
    ]
};

mix.webpackConfig(webpackConfig);

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
mix.less('resources/assets/less/css/app.less', 'public/compiled/css/app-compiled.css');

mix.combine([
    'public/compiled/css/app-compiled.css',
    'resources/assets/less/css/animate.min.css'
], 'public/compiled/css/stylesheet.css');

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
    'bower_components/jquery-idletimer/dist/idle-timer.js',
    'bower_components/select2/dist/js/select2.js',
    'bower_components/webix/codebase/webix.js',
    'bower_components/bootstrap/dist/js/bootstrap.js',
    'bower_components/bootstrap-select/dist/js/bootstrap-select.js',
    'public/js/typeahead.bundle.js',
    'public/js/DateTimePicker.min.js',
], 'public/compiled/js/issue-688.js');
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
], 'public/compiled/js/admin-ui.js');
/** end fixing admin-ui */

//apps
mix.js('resources/assets/js/app.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/app-provider-ui.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/app-ccd-importer.js', 'public/compiled/js').sourceMaps();

mix.version([
    'public/js/*.*',
    'public/js/admin/*.*',
    'public/js/admin/reports/*.*',
    'public/js/ccd/*.*',
    'public/js/patient/*.*',
    'public/js/polyfills/*.*',
    'public/js/rules/*.*',
    'public/js/wpUsers/*.*',

    'public/css/*.*',

    'public/img/*.*',
    'public/img/ui/*.*',
    'public/img/emails/*.*',
    'public/img/emails/careplan-pending-approvals/*.*',
    'public/img/landing-pages/*.*',

    'public/vendor/datatables-images/*.*',

    'public/webix/codebase/*.*'
]);