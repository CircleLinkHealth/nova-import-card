"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const fs = require("fs");
function fileExists(filePath) {
    return new Promise((resolve) => {
        fs.access(filePath, (err => resolve(!err)));
    });
}
exports.fileExists = fileExists;
function readFromFile(filePath) {
    return new Promise((resolve, reject) => {
        fs.readFile(filePath, 'utf8', (err, data) => {
            if (err) {
                return reject(err);
            }
            resolve(data);
        });
    });
}
exports.readFromFile = readFromFile;
function storeToFile(filePath, data) {
    return new Promise((resolve, reject) => {
        let str = data;
        if (typeof data !== "string") {
            str = JSON.stringify(data);
        }
        fs.writeFile(filePath, str, function (err) {
            if (err) {
                return reject(err);
            }
            resolve();
        });
    });
}
exports.storeToFile = storeToFile;
//# sourceMappingURL=disk.js.map