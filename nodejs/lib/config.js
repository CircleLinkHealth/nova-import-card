"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
class Configuration {
    constructor() {
        this.storeResultsInDb = Configuration.parseBool(process.env.CCDA_PARSER_STORE_RESULTS_IN_DB, false);
        this.dbUri = process.env.CCDA_PARSER_DB_URI;
        this.dbHost = process.env.CCDA_PARSER_DB_HOST || "127.0.0.1";
        this.dbPort = Configuration.parseNumber(process.env.CCDA_PARSER_DB_PORT, 3306);
        this.dbName = process.env.CCDA_PARSER_DB_DATABASE;
        this.dbUsername = process.env.CCDA_PARSER_DB_USERNAME;
        this.dbPassword = process.env.CCDA_PARSER_DB_PASSWORD;
    }
    static get() {
        if (!Configuration._singleton) {
            Configuration._singleton = new Configuration();
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
//# sourceMappingURL=config.js.map