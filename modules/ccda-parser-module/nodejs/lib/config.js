"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const minimist = require('minimist');
class Configuration {
    constructor(args) {
        const processed = minimist(args, {
            boolean: ['store-results-in-db', 'force'],
            default: {
                'store-results-in-db': true,
                'db-host': '127.0.0.1',
                'db-port': 3306,
                'db-name': 'cpm_local',
                'db-username': 'root',
                'db-password': '',
                'db-json-table': 'ccdas-json',
                'force': false
            }
        });
        this.storeResultsInDb = processed['store-results-in-db'];
        this.dbUri = processed['db-uri'];
        this.dbHost = processed['db-host'];
        this.dbPort = +processed['db-port'];
        this.dbName = processed['db-name'];
        this.dbUsername = processed['db-username'];
        this.dbPassword = processed['db-password'];
        this.dbJsonTable = processed['db-json-table'];
        this.force = processed['force'];
        this.ccdaId = processed['ccda-id'];
        this.ccdaXmlPath = processed['ccda-xml-path'];
        this.ccdaJsonTargetPath = processed['ccda-json-target-path'];
    }
    static get() {
        if (!Configuration._singleton) {
            Configuration._singleton = new Configuration(process.argv.slice(2));
        }
        return Configuration._singleton;
    }
    static parseBool(val, defaultVal) {
        if (!val)
            return defaultVal;
        if (typeof val === "boolean") {
            return val;
        }
        if (typeof val === "number") {
            return val > 0;
        }
        if (typeof val === "string") {
            return val.toLowerCase() == "true";
        }
        return defaultVal;
    }
    static parseNumber(val, defaultValue) {
        if (val && !isNaN(+val)) {
            return +val;
        }
        return defaultValue;
    }
}
exports.default = Configuration;
//this triggers initialization. should be called at the very beginning of the process
Configuration.get();
//# sourceMappingURL=config.js.map