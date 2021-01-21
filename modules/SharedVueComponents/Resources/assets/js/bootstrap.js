window._ = require('lodash');
window.Popper = require('popper.js').default;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {
}

require('./bootstrap-axios');
require('./logger-logdna').init();
// Commented out because on vapor it chooses the cloudfront asset URL as the base URL, instead of CPM.
// As a result, instead of auth requests going to cpm.com/broadcasting/auth, they go to cloudfronturl.com/broadcasting/auth
require('./pusher-echo').init();


