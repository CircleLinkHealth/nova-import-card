"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const mysql_1 = require("mysql");
const config_1 = require("./config");
const db_create_table_1 = require("./db-create-table");
let dbTable = 'ccdas_v2';
let connection;
function init() {
    return new Promise((resolve, reject) => {
        const config = config_1.default.get();
        if (!config.storeResultsInDb) {
            return;
        }
        try {
            if (config.dbUri) {
                connection = mysql_1.createConnection(config.dbUri);
            }
            else {
                connection = mysql_1.createConnection({
                    host: config.dbHost,
                    port: config.dbPort,
                    database: config.dbName,
                    user: config.dbUsername,
                    password: config.dbPassword,
                });
            }
            //now make sure that table exists
            connection.query(db_create_table_1.CREATE_TABLE_COMMAND, function (err, result) {
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
exports.init = init;
function getFromDb(ccdaId, fields) {
    const config = config_1.default.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
    return new Promise((resolve, reject) => {
        connection.query(`SELECT ${fields.join(',')} FROM ${dbTable} WHERE ccda_id = ?`, [ccdaId], (error, results, fields) => {
            if (error) {
                reject(error);
            }
            else {
                resolve(results[0]);
            }
        });
    });
}
exports.getFromDb = getFromDb;
function updateDb(ccdaId, status, result, error) {
    const config = config_1.default.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
    return new Promise((resolve, reject) => {
        getFromDb(ccdaId, ['id'])
            .then(res => {
            if (res) {
                return performUpdate(ccdaId, status, result, error);
            }
            else {
                return performInsert(ccdaId, status, result, error);
            }
        })
            .then(resolve)
            .catch(err => reject(err));
    });
}
exports.updateDb = updateDb;
function performInsert(ccdaId, status, result, error) {
    return new Promise((resolve, reject) => {
        const fields = ['ccda_id', 'status'];
        const values = [ccdaId, status];
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
        connection.query(cmd, values, (error, results) => {
            if (error) {
                reject(error);
            }
            else {
                resolve();
            }
        });
    });
}
function performUpdate(ccdaId, status, result, error) {
    return new Promise((resolve, reject) => {
        const fields = ['ccda_id=?', 'status=?'];
        const values = [ccdaId, status];
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
        connection.query(cmd, values, (error, results) => {
            if (error) {
                reject(error);
            }
            else {
                resolve();
            }
        });
    });
}
//# sourceMappingURL=db.js.map