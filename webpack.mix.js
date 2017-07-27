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



// mix.js('resources/assets/js/provider/view-care-plan.js', 'public/js');
mix.js('resources/assets/js/app-provider-ui.js', 'public/js');
mix.js('resources/assets/js/app-provider-admin-panel-ui.js', 'public/js');
mix.js('resources/assets/js/app-clh-admin-ui.js', 'public/js');


//DO NOT RECOMPILE THE FOLLOWING.
//THEY HAVE NOT BEEN MIGRATED TO VUE2 YET
// mix.webpack('uploader.js');
// mix.webpack('ccd-models/items/medicationItem.js');
// mix.webpack('ccd-models/items/allergiesItem.js');
// mix.webpack('ccd-models/items/problemsItem.js');