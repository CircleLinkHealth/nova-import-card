const minimist = require('minimist');
export default class Configuration {

    private static _singleton: Configuration;
    public readonly storeResultsInDb: boolean;
    public readonly dbUri: string;
    public readonly dbHost: string;
    public readonly dbPort: number;
    public readonly dbName: string;
    public readonly dbUsername: string;
    public readonly dbPassword: string;
    public readonly dbJsonTable: string;
    public readonly force: boolean;
    public readonly ccdaId: string;
    public readonly ccdaXmlPath: string;
    public readonly ccdaJsonTargetPath: string;

    constructor(args: any) {
        const processed: { [key: string]: string | boolean } = minimist(args, {
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

        this.storeResultsInDb = processed['store-results-in-db'] as boolean;
        this.dbUri = processed['db-uri'] as string;
        this.dbHost = processed['db-host'] as string;
        this.dbPort = +(processed['db-port'] as string);
        this.dbName = processed['db-name'] as string;
        this.dbUsername = processed['db-username'] as string;
        this.dbPassword = processed['db-password'] as string;
        this.dbJsonTable = processed['db-json-table'] as string;

        this.force = processed['force'] as boolean;
        this.ccdaId = processed['ccda-id'] as string;
        this.ccdaXmlPath = processed['ccda-xml-path'] as string;
        this.ccdaJsonTargetPath = processed['ccda-json-target-path'] as string;
    }

    public static get() {
        if (!Configuration._singleton) {
            Configuration._singleton = new Configuration(process.argv.slice(2));
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

//this triggers initialization. should be called at the very beginning of the process
Configuration.get();