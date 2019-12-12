import {Connection, createConnection, FieldInfo, MysqlError} from "mysql";
import Configuration from "./config";
import {CREATE_TABLE_COMMAND} from "./db-create-table";

let dbTable = 'ccdas_v2';
let connection: Connection;

export function init(): Promise<void> {
    return new Promise<void>((resolve, reject) => {
        const config = Configuration.get();
        if (!config.storeResultsInDb) {
            return;
        }

        try {
            if (config.dbUri) {
                connection = createConnection(config.dbUri);
            } else {
                connection = createConnection({
                    host: config.dbHost,
                    port: config.dbPort,
                    database: config.dbName,
                    user: config.dbUsername,
                    password: config.dbPassword,
                });
            }

            //now make sure that table exists
            connection.query(CREATE_TABLE_COMMAND, function (err, result) {
                if (err) {
                    return reject(err);
                }
                resolve();
            });
        }
        catch (e) {
            reject(e);
        }
    });
}

export function getFromDb(ccdaId: string, fields: string[]): Promise<any> {
    const config = Configuration.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }

    return new Promise<any>((resolve, reject) => {
        connection.query(`SELECT ${fields.join(',')} FROM ${dbTable} WHERE ccda_id = ?`,
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
        getFromDb(ccdaId, ['id'])
            .then(res => {

                if (res) {
                    return performUpdate(ccdaId, status, result, error);
                } else {
                    return performInsert(ccdaId, status, result, error);
                }

            })
            .then(resolve)
            .catch(err => reject(err));
    });
}

function performInsert(ccdaId: string, status: string, result?: string, error?: string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
        const fields = ['ccda_id', 'status'];
        const values: any[] = [ccdaId, status];
        if (result) {
            fields.push('result');
            values.push(result);
        }
        if (error) {
            fields.push('error');
            values.push(error);
        }

        fields.push('created_at', 'updated_at');
        values.push(new Date(), new Date());

        const cmd = `INSERT INTO ${dbTable} (${fields.join(',')}) VALUES (${fields.map(f => '?').join(',')})`;
        connection.query(cmd,
            values,
            (error: MysqlError | null, results: any[]) => {
                if (error) {
                    reject(error);
                } else {
                    resolve();
                }
            });
    });
}

function performUpdate(ccdaId: string, status: string, result?: string, error?: string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
        const fields = ['ccda_id=?', 'status=?'];
        const values: any[] = [ccdaId, status];
        if (result) {
            fields.push("result=?");
            values.push(result);
        }
        if (error) {
            fields.push('error=?');
            values.push(error);
        }

        fields.push('updated_at=?');
        values.push(new Date());
        values.push(ccdaId);

        const cmd = `UPDATE ${dbTable} SET ${fields.join(',')} WHERE ccda_id = ?`;
        connection.query(cmd,
            values,
            (error: MysqlError | null, results: any[]) => {
                if (error) {
                    reject(error);
                } else {
                    resolve();
                }
            });
    });
}