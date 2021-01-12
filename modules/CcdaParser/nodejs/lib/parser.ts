
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
                care_plan: parsedCcd.data.care_plan,
                chief_complaint: parsedCcd.data.chief_complaint,
                demographics: parsedCcd.data.demographics,
                encounters: parsedCcd.data.encounters,
                functional_statuses: parsedCcd.data.functional_statuses,
                immunizations: parsedCcd.data.immunizations,
                instructions: parsedCcd.data.instructions,
                results: parsedCcd.data.results,
                medications: parsedCcd.data.medications,
                payers: parsedCcd.data.payers,
                problems: parsedCcd.data.problems,
                procedures: parsedCcd.data.procedures,
                smoking_status: parsedCcd.data.smoking_status,
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

