import {rootUrl} from "./app.config";

window.Echo = require('laravel-echo');
window.Pusher = require('pusher-js');
const options = {
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_KEY,
    cluster: process.env.MIX_PUSHER_CLUSTER,
    encrypted: true,
    forceTLS: ! ['local', 'testing'].includes(process.env.MIX_APP_ENV),
    authEndpoint: rootUrl('/broadcasting/auth')
};

let keyExists = options.key !== undefined && options.key !== null

if (!keyExists) {
    console.log('pusher app key not found')
}

export default {
    init: () => {
        if (keyExists) {
            window.EchoPusher = new window.Echo.default(options);
            console.log('echo:success')
        } else {
            console.log('echo:failed')
        }
    },
};


