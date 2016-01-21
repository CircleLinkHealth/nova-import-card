var elixir = require('laravel-elixir');

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
    mix.less('/css/app.less', 'public/css/stylesheet.css');
    mix.less('/css/wpstyle.less', 'public/css/wpstyle.css');
    mix.browserify('/ccd/uploader.js');

    //mix.browserify('/ccd/parser/bluebutton.min.js');

    mix.browserify('ccd/viewer/ccd.js', 'resources/assets/js/compiled/ccd/viewer');
    //mix.browserify('ccd/viewer/demographics.js', 'resources/assets/js/compiled/ccd/viewer');
    //mix.browserify('ccd/viewer/document.js', 'resources/assets/js/compiled/ccd/viewer');

    mix.scriptsIn('resources/assets/js/compiled/ccd/viewer', 'public/js/ccd/viewer.js');
    mix.copy('resources/assets/js/ccd/parser/bluebutton.min.js', 'public/js/ccd/bluebutton.min.js');
});
