/**
 set @id = (select id
 from users
 where display_name like '%margarita fe%');

 set @nurseUserId = (select id from nurse_info where user_id = @id);

 select invoice_data from nurse_invoices where nurse_info_id = @nurseUserId order by month_year desc limit 1;
 *
 */

const invoice = {
};

let visitsToPatientIds = [];

function calculate(collection) {
    for (let patientId in collection) {
        const dates = collection[patientId];
        if (Array.isArray(dates) && dates.length === 0) {
            continue;
        }
        let visitCount = 0;
        for (let dateKey in dates) {
            const entry = dates[dateKey];
            visitCount += entry.count;
        }
        if (!visitsToPatientIds[patientId]) {
            visitsToPatientIds[patientId] = 0;
        }
        visitsToPatientIds[patientId] += visitCount;
    }
}

calculate(invoice.visits);
calculate(invoice.bhiVisits);
calculate(invoice.pcmVisits);

let str = [];
let totalCount = 0;
for (let patientId in visitsToPatientIds) {
    str.push(`${patientId}: ${visitsToPatientIds[patientId]}`);
    totalCount += visitsToPatientIds[patientId];
}
console.log(str.join('\n'));
console.log(Object.keys(visitsToPatientIds).length);
console.log(totalCount);
