const mix = require('laravel-mix');
const path = require('path');
const fs = require('fs');
const SentryWebpackPlugin = require("@sentry/webpack-plugin");

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


/**
 *
 * JS
 *
 */

mix.combine([
    'bower_components/jquery/dist/jquery.js',
    'bower_components/jquery-ui/jquery-ui.js',
    'bower_components/jquery-idletimer/dist/idle-timer.js',
    'bower_components/select2/dist/js/select2.js',
    'bower_components/webix/codebase/webix.js',
    'bower_components/bootstrap/dist/js/bootstrap.js',
    'bower_components/bootstrap-select/dist/js/bootstrap-select.js',
    'public/js/typeahead.bundle.js',
], 'public/compiled/js/issue-688.js');

mix.js('resources/assets/js/app-provider-ui.js', 'public/compiled/js').sourceMaps();
mix.js('resources/assets/js/app-enrollment-ui.js', 'public/compiled/js').sourceMaps();

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

const walkSync = function (dir, fileList) {
    const files = fs.readdirSync(dir);
    fileList = fileList || [];
    files.forEach(function (file) {
        if (fs.statSync(path.join(dir, file)).isDirectory()) {
            fileList = walkSync(path.join(dir, file), fileList);
        } else {
            fileList.push(path.join(dir, file));
        }
    });
    return fileList;
};
const allPublicFiles = walkSync('public');
const toVersion = [];
allPublicFiles.forEach((fullPath) => {

    const dirName = path.dirname(fullPath);

    //looking for compiled folder
    if (dirName.indexOf('compiled') > -1) {
        //we assume this file is already processed and ignore
        return;
    }

    const fileName = path.basename(fullPath);

    if (fileName.indexOf('chunk-') > -1) {
        //we assume this file is already processed and ignore
        return;
    }


    if ([".css", ".img", ".jpg", "jpeg", ".js", ".png", ".ico", ".svg", ".json"].includes(path.extname(fullPath))) {
        toVersion.push(fullPath);
    }

});
mix
    .sourceMaps()
    .version(toVersion);
