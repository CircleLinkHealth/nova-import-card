export default class Configuration {

    public readonly storeResultsInDb: boolean = Configuration.parseBool(process.env.CCDA_PARSER_STORE_RESULTS_IN_DB, false);
    public readonly dbUri: string = process.env.CCDA_PARSER_DB_URI;
    public readonly dbHost: string = process.env.CCDA_PARSER_DB_HOST || "127.0.0.1";
    public readonly dbPort: number = Configuration.parseNumber(process.env.CCDA_PARSER_DB_PORT, 3306);
    public readonly dbName: string = process.env.CCDA_PARSER_DB_DATABASE;
    public readonly dbUsername: string = process.env.CCDA_PARSER_DB_USERNAME;
    public readonly dbPassword: string = process.env.CCDA_PARSER_DB_PASSWORD;

    private static _singleton: Configuration;
    public static get() {
        if (!Configuration._singleton) {
            Configuration._singleton = new Configuration();
        }
        return Configuration._singleton;
    }

    private static parseBool(val: any, defaultVal: boolean) {
        if (!val) return defaultVal;
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

    private static parseNumber(val: any, defaultValue: number) {
        if (val && !isNaN(+val)) {
            return +val;
        }
        return defaultValue;
    }

}