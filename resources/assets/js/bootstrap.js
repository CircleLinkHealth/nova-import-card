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
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

import pusher_js from "pusher-js";

window.Pusher = pusher_js;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'dca1637b1d26e1c5867f',
    cluster: 'mt1',
    encrypted: true
});

window.Echo.channel('pusher-test').listen('PusherTest', e => {
    console.log('Hey man new notification you have '+ e.message.id +'');
    console.log(e);
});