"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
require("mocha");
const path = require("path");
const child_process_1 = require("child_process");
describe('test by calling function', function () {
    it('should spawn parser process and process file', function (done) {
        const filePath = path.join(__dirname, '..', 'samples', 'nist.xml');
        const target = path.join(__dirname, '..', 'parsed-ccds', 'nist-exec.json');
        child_process_1.exec(`node ../index ${filePath} ${target}`, {
            cwd: __dirname
        }, function (err, stdout, stderr) {
            console.log(stdout);
            if (err) {
                return done(err);
            }
            if (stderr) {
                return done(new Error(stderr));
            }
            done();
        });
    });
});
//# sourceMappingURL=spawn-process.js.map