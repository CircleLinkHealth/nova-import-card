
//for some reason this is needed, otherwise blue-button throws exception
const ejs = require('ejs');
ejs.filters = {};

const BlueButton = require('../services/blue-button');
export function parse(xmlBody: string): BluebuttonParseResponse {
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

    } catch (ex) {
        return {
            error: ex,
            data: null
        }
    }
}

interface BluebuttonParseResponse {
    error?: Error;
    data: any;
}

