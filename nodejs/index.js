"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const config_1 = require("./lib/config");
const disk_1 = require("./lib/disk");
const parser_1 = require("./lib/parser");
const db_1 = require("./lib/db");
const config = config_1.default.get();
const args = process.argv.slice(2);
console.log('Looking for ccda id, input filename in args.');
let fileId = args[0];
if (!fileId) {
    console.error("you need to specify ccda id");
    process.exit(1);
}
let filePath = args[1];
if (!filePath) {
    console.error("you need to specify a source file path");
    process.exit(1);
}
let targetFilePath = null;
if (!config.storeResultsInDb) {
    console.log('Also looking for output filename because we are not saving to db.');
    targetFilePath = args[2];
    if (!targetFilePath) {
        console.error("you need to specify a target path");
        process.exit(1);
    }
}
function processAndStoreInDb(ccdaId, filePath) {
    let dataStr;
    return disk_1.readFromFile(filePath)
        .then(data => {
        dataStr = data;
        return db_1.updateDb(fileId, "in_progress");
    })
        .then(() => {
        const result = parser_1.parse(dataStr);
        return db_1.updateDb(fileId, 'completed', result.data, result.error.stack);
    })
        .then(() => Promise.resolve());
}
function processAndStoreOnDisk(ccdaId, filePath, targetFilePath) {
    return disk_1.readFromFile(filePath)
        .then(data => {
        const result = parser_1.parse(data);
        return disk_1.storeToFile(targetFilePath, result);
    });
}
if (config.storeResultsInDb) {
    db_1.init();
    db_1.getFromDb(fileId)
        .then(res => {
        if (res) {
            console.log("Record already exists in db with same ccda id. Exiting.");
            return Promise.resolve();
        }
        return processAndStoreInDb(fileId, filePath);
    })
        .then(() => {
        console.log("Done");
        process.exit(0);
    })
        .catch(err => {
        console.error(err);
        process.exit(1);
    });
}
else {
    disk_1.fileExists(targetFilePath)
        .then(exists => {
        if (exists) {
            console.log("Same file as target filename already exists.");
            return Promise.resolve();
        }
        return processAndStoreOnDisk(fileId, filePath, targetFilePath);
    })
        .then(() => {
        console.log("Done");
        process.exit(0);
    })
        .catch(err => {
        console.error(err);
        process.exit(1);
    });
}
//# sourceMappingURL=index.js.map