import {Connection, createConnection, FieldInfo, MysqlError} from "mysql";
import Configuration from "./config";

let connection: Connection;

export function init() {
    const config = Configuration.get();
    if (!config.storeResultsInDb) {
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
    const config = Configuration.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }

    return new Promise<any>((resolve, reject) => {
        connection.query('SELECT * FROM ccdas WHERE id = ?',
            [ccdaId],
            (error: MysqlError | null, results: any[], fields: FieldInfo[] | undefined) => {
                if (error) {
                    reject(error);
                } else {
                    resolve(results[0]);
                }
            });
    });
}

export function updateDb(ccdaId: string, status: string, result?: string, error?: string): Promise<void> {

    const config = Configuration.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }

    return new Promise<void>((resolve, reject) => {

        //TODO
        const insertCommand = '';
        connection.query(insertCommand,
            [ccdaId, status, result || null, error || null],
            (error: MysqlError | null, results: any[], fields: FieldInfo[] | undefined) => {
                if (error) {
                    reject(error);
                } else {
                    resolve();
                }
            });
    });
}