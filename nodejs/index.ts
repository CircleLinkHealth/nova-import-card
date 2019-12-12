import Configuration from "./lib/config";
import {fileExists, readFromFile, storeToFile} from "./lib/disk";
import {parse} from "./lib/parser";
import {getFromDb, updateDb} from "./lib/db";

const config = Configuration.get();

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

let targetFilePath: string = null;
if (!config.storeResultsInDb) {
    console.log('Also looking for output filename because we are not saving to db.');
    targetFilePath = args[2];
    if (!targetFilePath) {
        console.error("you need to specify a target path");
        process.exit(1);
    }
}

function exitWithError(code: number, msg: string) {
    if (code > 0) {
        console.error(msg);
    } else {
        console.log(msg);
    }
    process.exit(code);
}

function processAndStoreInDb(ccdaId: string, filePath: string): Promise<void> {
    let dataStr: string;
    return readFromFile(filePath)
        .then(data => {
            dataStr = data;
            return updateDb(fileId, "in_progress");
        })
        .then(() => {
            const result = parse(dataStr);
            return updateDb(fileId, 'completed', result.data, result.error.stack);
        })
        .then(() => Promise.resolve());
}

function processAndStoreOnDisk(ccdaId: string, filePath: string, targetFilePath: string) {
    return readFromFile(filePath)
        .then(data => {
            const result = parse(data);
            return storeToFile(targetFilePath, result);
        });
}

if (config.storeResultsInDb) {
    getFromDb(fileId)
        .then(res => {
            if (res) {
                console.log("Record already exists in db with same ccda id. Exiting.")
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
        });
} else {
    fileExists(targetFilePath)
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
        })
}