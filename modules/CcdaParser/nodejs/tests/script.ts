import 'mocha';
import * as fs from "fs";
import * as path from "path";

import {parse} from '../lib/parser';

describe('test by calling function', function () {

    it('should parse a small ccd', function (done) {
        const filePath = path.join(__dirname, '..', 'samples', 'nist.xml');
        fs.readFile(filePath, 'utf8', function (err, data) {

            if (err) {
                return done(err);
            }

            if (!data || !data.length) {
                return done(new Error('data is empty'));
            }

            const result = parse(data);
            done(result.error);
        });
    });

});