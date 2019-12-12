import {Connection, createConnection} from "mysql";
import Configuration from "./config";

let connection: Connection;

export function init() {
    const config = Configuration.get();
    if (config.storeResultsInDb) {
        return;
    }

    if (config.dbUri) {
        connection = createConnection(config.dbUri);
    } else {
        connection = createConnection({
            host: config.dbHost,
            port: config.dbPort,
            database: config.dbName,
            user: config.dbUsername,
            password: config.dbPassword
        });
    }
}

export function getFromDb(ccdaId: string): Promise<any> {
    return new Promise<any>((resolve, reject) => {

    });
}

export function updateDb(ccdaId: string, status: string, result?: string, error?: string): Promise<void> {

    const config = Configuration.get();
    if (config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }

    return Promise.resolve();
}