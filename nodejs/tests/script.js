"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
require("mocha");
const fs = require("fs");
const path = require("path");
const parser_1 = require("../lib/parser");
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
            const result = parser_1.parse(data);
            done(result.error);
        });
    });
});
//# sourceMappingURL=script.js.map