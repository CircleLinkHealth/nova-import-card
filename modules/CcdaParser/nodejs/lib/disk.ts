import * as fs from "fs";

export function fileExists(filePath: string): Promise<boolean> {
    return new Promise<boolean>((resolve) => {
        fs.access(filePath, (err => resolve(!err)));
    });
}

export function readFromFile(filePath: string): Promise<string> {
    return new Promise<string>((resolve, reject) => {
        fs.readFile(filePath, 'utf8', (err, data) => {
            if (err) {
                return reject(err);
            }
            resolve(data);

        });
    });
}

export function storeToFile(filePath: string, data: any | string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
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

