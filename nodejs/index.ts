import {parse} from './lib/parser';
import * as fs from "fs";

const args = process.argv.slice(2);

let filePath = args[0];
if (!filePath) {
    console.error("you need to specify a source file path");
    process.exit(1);
}

let targetFilePath = args[1];
if (!targetFilePath) {
    console.error("you need to specify a target path");
    process.exit(1);
}

fs.readFile(filePath, 'utf8', (err, data) => {
    if (err) {
        console.error(err);
        process.exit(1);
    }

    const start = Date.now();
    console.log('ready to parse');
    const result = parse(data);
    console.log('parsing complete', (Date.now() - start) / 1000, 'secs');
    if (result.error) {
        console.error(result.error);
        process.exit(1);
    }

    fs.writeFile(targetFilePath, JSON.stringify(result.data), function (err) {
        if (err) {
            console.error(err);
            process.exit(1);
        }
        process.exit(0);
    });
});
