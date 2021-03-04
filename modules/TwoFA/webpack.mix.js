const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');


const webpackConfig = {
    resolve: {
        alias: {
            TwoFA: path.resolve(__dirname, './'),
        },
        modules: [
            path.resolve(__dirname, 'node_modules'),
            path.resolve(__dirname, './'),
        ]
    }
};

mix.webpackConfig(webpackConfig);

mix.setPublicPath('../../public').mergeManifest();

mix.js(__dirname + '/Resources/assets/js/app.js', 'js/twofa.js')
    .sass( __dirname + '/Resources/assets/sass/app.scss', 'css/twofa.css');

if (mix.inProduction()) {
    mix.version();
}