"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
//for some reason this is needed, otherwise blue-button throws exception
const ejs = require('ejs');
ejs.filters = {};
const BlueButton = require('../services/blue-button');
function parse(xmlBody) {
    try {
        const parsedCcd = BlueButton(xmlBody);
        return {
            error: null,
            data: {
                type: parsedCcd.type,
                document: parsedCcd.data.document,
                allergies: parsedCcd.data.allergies,
                demographics: parsedCcd.data.demographics,
                medications: parsedCcd.data.medications,
                payers: parsedCcd.data.payers,
                problems: parsedCcd.data.problems,
                vitals: parsedCcd.data.vitals
            }
        };
    }
    catch (ex) {
        return {
            error: ex,
            data: null
        };
    }
}
exports.parse = parse;
//# sourceMappingURL=parser.js.map