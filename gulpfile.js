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
    mix.less(['/css/app.less', '/css/material.min.css'], 'public/css/stylesheet.css');
    mix.browserify('/ccd/uploader.js');

    mix.scripts([
        'resources/assets/js/ccd/parser/bluebutton.min.js',
        'resources/assets/js/material.min.js'
    ], 'public/js/scripts.js');
});
