const raygun = require('./raygun').getRaygun();
const sentry = require('./sentry').getSentry();

module.exports = {
    reportsToRaygun: function () {
        return raygun !== null;
    },
    reportsToSentry: function () {
        return sentry !== null;
    },
    getErrorLogger: function () {
        return {
            report: function (err, options, callback) {
                if (sentry) {
                    sentry.captureException(err);
                    if (callback) {
                        callback();
                    }
                }
                if (raygun) {
                    raygun.send(err, options, callback);
                }
            }
        };
    },
};
