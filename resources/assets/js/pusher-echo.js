
window.Pusher = require('pusher-js');
const options = {
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
};

module.exports = {
    init: () => {
        if (window.Echo && options.key !== undefined && options.key !== null) {
            window.EchoPusher = new window.Echo(options);
        } else {
            window.EchoPusher = console
        }
    },
};


