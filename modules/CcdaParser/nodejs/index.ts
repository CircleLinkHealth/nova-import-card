import Configuration from "./lib/config";
import {fileExists, readFromFile, storeToFile} from "./lib/disk";
import {parse} from "./lib/parser";
import {getJsonFromDb, init as initDb, updateDb} from "./lib/db";

const config = Configuration.get();

console.log('Looking for ccda id, input filename in args.');
const ccdaId = config.ccdaId;
if (!ccdaId) {
    console.error("you need to specify ccda id");
    process.exit(1);
}

const ccdaXmlPath: string = config.ccdaXmlPath;
if (!ccdaXmlPath) {
    console.error("you need to specify a source file path");
    process.exit(1);
}

let ccdaJsonTargetPath: string = config.ccdaJsonTargetPath;
if (!config.storeResultsInDb) {
    console.log('Also looking for output filename because we are not saving to db.');
    if (!ccdaJsonTargetPath) {
        console.error("you need to specify a target path");
        process.exit(1);
    }
}

function processAndStoreInDb(ccdaId: string, filePath: string): Promise<void> {
    let dataStr: string;
    return readFromFile(filePath)
        .then(data => {
            dataStr = data;
            return updateDb(ccdaId, "in_progress");
        })
        .then(() => {
            console.log('Ready to parse');
            const start = Date.now();
            const result = parse(dataStr);
            const durationSeconds = Math.round((Date.now() - start) / 1000);
            return updateDb(ccdaId, 'completed', result.data ? JSON.stringify(result.data) : undefined, result.error ? result.error.stack : undefined, durationSeconds);
        })
        .then(() => Promise.resolve());
}

function processAndStoreOnDisk(ccdaId: string, filePath: string, targetFilePath: string) {
    return readFromFile(filePath)
        .then(data => {
            console.log('Ready to parse');
            const result = parse(data);
            return storeToFile(targetFilePath, result.data ? JSON.stringify(result.data) : undefined);
        });
}

if (config.storeResultsInDb) {
    initDb()
        .then(() => getJsonFromDb(ccdaId, ['id', 'ccda_id', 'status']))
        .then(res => {
            if (res) {
                if (config.force) {
                    console.log('Record already exists in db but force is set to true. Will override.');
                } else {
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
} else {
    fileExists(ccdaJsonTargetPath)
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
        })
}