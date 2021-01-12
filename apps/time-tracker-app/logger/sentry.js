let sentryClient = null;
if (process.env.SENTRY_DSN) {
    const Sentry = require('@sentry/node');
    Sentry.init({ dsn: process.env.SENTRY_DSN });
    sentryClient = Sentry;
}

module.exports = {
    getSentry: function () {
        return sentryClient;
    }
};