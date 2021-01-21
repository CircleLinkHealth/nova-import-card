"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const config_1 = require("./lib/config");
const disk_1 = require("./lib/disk");
const parser_1 = require("./lib/parser");
const db_1 = require("./lib/db");
const config = config_1.default.get();
console.log('Looking for ccda id, input filename in args.');
const ccdaId = config.ccdaId;
if (!ccdaId) {
    console.error("you need to specify ccda id");
    process.exit(1);
}
const ccdaXmlPath = config.ccdaXmlPath;
if (!ccdaXmlPath) {
    console.error("you need to specify a source file path");
    process.exit(1);
}
let ccdaJsonTargetPath = config.ccdaJsonTargetPath;
if (!config.storeResultsInDb) {
    console.log('Also looking for output filename because we are not saving to db.');
    if (!ccdaJsonTargetPath) {
        console.error("you need to specify a target path");
        process.exit(1);
    }
}
function processAndStoreInDb(ccdaId, filePath) {
    let dataStr;
    return disk_1.readFromFile(filePath)
        .then(data => {
        dataStr = data;
        return db_1.updateDb(ccdaId, "in_progress");
    })
        .then(() => {
        console.log('Ready to parse');
        const start = Date.now();
        const result = parser_1.parse(dataStr);
        const durationSeconds = Math.round((Date.now() - start) / 1000);
        return db_1.updateDb(ccdaId, 'completed', result.data ? JSON.stringify(result.data) : undefined, result.error ? result.error.stack : undefined, durationSeconds);
    })
        .then(() => Promise.resolve());
}
function processAndStoreOnDisk(ccdaId, filePath, targetFilePath) {
    return disk_1.readFromFile(filePath)
        .then(data => {
        console.log('Ready to parse');
        const result = parser_1.parse(data);
        return disk_1.storeToFile(targetFilePath, result.data ? JSON.stringify(result.data) : undefined);
    });
}
if (config.storeResultsInDb) {
    db_1.init()
        .then(() => db_1.getJsonFromDb(ccdaId, ['id', 'ccda_id', 'status']))
        .then(res => {
        if (res) {
            if (config.force) {
                console.log('Record already exists in db but force is set to true. Will override.');
            }
            else {
                console.log(`Record already exists in db with same ccda id. Status[${res.status}]. Exiting.`);
                return Promise.resolve();
            }
        }
        return processAndStoreInDb(ccdaId, ccdaXmlPath);
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
    disk_1.fileExists(ccdaJsonTargetPath)
        .then(exists => {
        if (exists) {
            console.log("Same file as target filename already exists.");
            return Promise.resolve();
        }
        return processAndStoreOnDisk(ccdaId, ccdaXmlPath, ccdaJsonTargetPath);
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