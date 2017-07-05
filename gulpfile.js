const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

elixir((mix) => {
    // mix.phpUnit([
    //     'tests/AprimaApi/*'
    // ]);
    // mix.less(['/css/wpstyle.less'], 'public/css/wpstyle.css');

    // PLEASE DON'T CHANGE THIS - michalis
    // mix.less([
    //     '/css/app.less',
    //     '/css/animate.min.css'
    // ], 'public/css/stylesheet.css');

    // mix.sass('fab.scss')
    // .sass([
    //     '/css/provider/dashboard.scss',
    //     './resources/assets/less/css/animate.min.css'
    // ], 'public/css/provider-dashboard.css');

    // mix.webpack('provider/create-locations.js');
    // mix.webpack('provider/create-staff.js');

    //DO NOT RE-COMPILE THOSE UNTIL WE FULLY MIGRATE TO VUE 2
    // mix.webpack('uploader.js');

    mix.webpack('importer-training.js');
    // mix.webpack('view-care-plan.js');
    // mix.webpack('components/CareTeam/care-person.js');

    // mix.webpack('ccd-models/items/medicationItem.js');
    // mix.webpack('ccd-models/items/allergiesItem.js');
    // mix.webpack('ccd-models/items/problemsItem.js');

    //mix.scripts([
    //    'resources/assets/js/material.min.js'
    //], 'public/js/scripts.js');
});
