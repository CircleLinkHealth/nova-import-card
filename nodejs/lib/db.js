"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const mysql_1 = require("mysql");
const config_1 = require("./config");
let connection;
function init() {
    const config = config_1.default.get();
    if (config.storeResultsInDb) {
        return;
    }
    if (config.dbUri) {
        connection = mysql_1.createConnection(config.dbUri);
    }
    else {
        connection = mysql_1.createConnection({
            host: config.dbHost,
            port: config.dbPort,
            database: config.dbName,
            user: config.dbUsername,
            password: config.dbPassword
        });
    }
}
exports.init = init;
function getFromDb(ccdaId) {
    return new Promise((resolve, reject) => {
    });
}
exports.getFromDb = getFromDb;
function updateDb(ccdaId, status, result, error) {
    const config = config_1.default.get();
    if (config.storeResultsInDb) {
        return Promise.reject(new Error("should not be called. not storing results in db."));
    }
    return Promise.resolve();
}
exports.updateDb = updateDb;
//# sourceMappingURL=db.js.map