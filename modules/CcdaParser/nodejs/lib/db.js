"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const mysql_1 = require("mysql");
const config_1 = require("./config");
const db_create_table_1 = require("./db-create-table");
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
            connection.query(db_create_table_1.createTableCommand(config.dbJsonTable), function (err, result) {
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
function getJsonFromDb(ccdaId, fields) {
    const config = config_1.default.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
    return new Promise((resolve, reject) => {
        connection.query(`SELECT ${fields.join(',')} FROM \`${config.dbJsonTable}\` WHERE ccda_id = ?`, [ccdaId], (error, results, fields) => {
            if (error) {
                reject(error);
            }
            else {
                resolve(results[0]);
            }
        });
    });
}
exports.getJsonFromDb = getJsonFromDb;
function updateDb(ccdaId, status, result, error, duration) {
    const config = config_1.default.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
    return new Promise((resolve, reject) => {
        getJsonFromDb(ccdaId, ['id'])
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
function performInsert(ccdaId, status, result, error, durationSeconds) {
    const config = config_1.default.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
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
        if (durationSeconds) {
            fields.push('duration_seconds');
            values.push(durationSeconds);
        }
        fields.push('created_at', 'updated_at');
        values.push(new Date(), new Date());
        const cmd = `INSERT INTO \`${config.dbJsonTable}\` (${fields.join(',')}) VALUES (${fields.map(f => '?').join(',')})`;
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
function performUpdate(ccdaId, status, result, error, durationSeconds) {
    const config = config_1.default.get();
    if (!config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
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
        if (durationSeconds) {
            fields.push('duration_seconds=?');
            values.push(durationSeconds);
        }
        fields.push('updated_at=?');
        values.push(new Date());
        values.push(ccdaId);
        const cmd = `UPDATE \`${config.dbJsonTable}\` SET ${fields.join(',')} WHERE ccda_id = ?`;
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