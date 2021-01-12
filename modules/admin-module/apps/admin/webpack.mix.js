const mix = require('laravel-mix');
const path = require('path');
const fs = require('fs');
const SentryWebpackPlugin = require("@sentry/webpack-plugin");
require('laravel-mix-merge-manifest');
mix.mergeManifest();

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
    devtool: 'source-map', // .vue is off by a line or 2. <template>, <style> sections are visible. file structure is clean
    // devtool: 'cheap-eval-source-map', // .vue lines are accurate. <template>, <style> are not visible. Lots of weird duplicate files, with ?ffcc, ?ddaa, etc. in the suffix.
    // devtool: 'cheap-module-source-map', // .vue lines are accurate, <template>, <style> sections are visible. But file structure is messed up, the actual debuggable js is in root directory, not in its subfolder where it is in actual source.
    output: {
        publicPath: "/",
        chunkFilename: 'compiled/js/[name].[chunkhash].js'
    },
    node: {
        fs: 'empty' //to help webpack resolve 'fs'
    },
    externals: [
        {
            './cptable': 'var cptable'
        }
    ],
    plugins: ['production', 'staging'].indexOf(process.env.MIX_APP_ENV) > -1 ? [
        new SentryWebpackPlugin({
            include: ".",
            ignoreFile: ".sentrycliignore",
            ignore: ["node_modules", "bower_components", "webpack.config.js"],
            configFile: "sentry.properties",
        }),
    ] : []
};

mix.webpackConfig(webpackConfig);

mix.combine([
    'node_modules/select2/dist/js/select2.js'
], 'public/compiled/js/dependencies.js');

mix.js('CircleLinkHealth/CpmAdmin/Resources/assets/js/app-provider-admin-panel-ui.js', 'public/compiled/js').sourceMaps();
mix.js('CircleLinkHealth/CpmAdmin/Resources/assets/js/app-clh-admin-ui.js', 'public/compiled/js').sourceMaps();

if (mix.inProduction()) {
    const ASSET_URL = process.env.ASSET_URL + "/";

    mix.webpackConfig(webpack => {
        return {
            plugins: [
                new webpack.DefinePlugin({
                    "process.env.ASSET_PATH": JSON.stringify(ASSET_URL)
                })
            ],
            output: {
                publicPath: ASSET_URL
            }
        };
    });
}