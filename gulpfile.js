var gulp = require('gulp');

var elixir = require('laravel-elixir');

elixir.config.js.browserify.transformers.push({
    name: 'vueify'
});

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    // mix.phpUnit([
    //     'tests/AprimaApi/*'
    // ]);
    // mix.less(['/css/wpstyle.less'], 'public/css/wpstyle.css');

    // PLEASE DON'T CHANGE THIS - michalis
    // mix.less([
    //     '/css/app.less',
    //     '/css/animate.min.css'
    // ], 'public/css/stylesheet.css');

    // mix.sass('fab.scss');

    // mix.browserify('uploader.js');
    // mix.browserify('ccd-models/items/medicationItem.js');
    // mix.browserify('ccd-models/items/allergiesItem.js');
    // mix.browserify('ccd-models/items/problemsItem.js');
    //mix.scripts([
    //    'resources/assets/js/material.min.js'
    //], 'public/js/scripts.js');
});
