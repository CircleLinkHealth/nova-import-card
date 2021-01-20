const LoggerModule = require('logdna');

const options = {
    app: 'CarePlan Manager',
    env: process.env['MIX_APP_ENV']
};

// Defaults to false, when true ensures meta object will be searchable
options.index_meta = true;

// Add tags in array or comma-separated string format:
options.tags = ['js', 'logdna'];

//in case logdna key is missing
let logger = console;

module.exports = {
    init: () => {
        const apiKey = process.env['MIX_LOG_DNA_CLIENT_INGESTION_KEY'];
        if (apiKey) {
            logger = LoggerModule.createLogger(apiKey, options);
        }
        window.Logger = logger;
    },
    Logger: logger
};