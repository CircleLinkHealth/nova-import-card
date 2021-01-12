import 'mocha';
import * as path from "path";
import {exec} from "child_process";

describe('test by calling function', function () {
    it('should spawn parser process and process file', function (done) {
        const filePath = path.join(__dirname, '..', 'samples', 'nist.xml');
        const target = path.join(__dirname, '..', 'parsed-ccds', 'nist-exec.json');
        exec(`node ../index ${filePath} ${target}`,
            {
                cwd: __dirname
            },
            function (err, stdout, stderr) {
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