"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.clearForPatient = exports.getTimeForCsId = exports.getTime = exports.getAll = exports.storeTime = exports.getCacheKey = void 0;
const UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];
const _usersTime = {};
function getCacheKey(info) {
    if (info.isFromCaPanel) {
        return `${info.providerId}`;
    }
    else {
        return `${info.providerId}-${info.patientId}`;
    }
}
exports.getCacheKey = getCacheKey;
function storeTime(key, activities, times) {
    const removeTimeFromCs = (csId, timeToRemove) => {
        for (let i = 0; i < times.length; i++) {
            const time = times[i];
            if (time.chargeable_service_id === csId) {
                time.time -= timeToRemove;
                break;
            }
        }
    };
    activities.forEach(a => {
        // remove time for activities that have force_skip or are included in the UNTRACKED_ROUTES array
        if (a.force_skip || UNTRACKED_ROUTES.indexOf(a.title) > -1) {
            console.debug(`activity[${a.title}] has force_skip, will not store in cache`);
            removeTimeFromCs(a.chargeable_service_id, a.duration);
        }
    });
    _usersTime[key] = times;
}
exports.storeTime = storeTime;
function getAll() {
    return _usersTime;
}
exports.getAll = getAll;
function getTime(key) {
    if (!_usersTime[key]) {
        return [];
    }
    return _usersTime[key];
}
exports.getTime = getTime;
function getTimeForCsId(key, csId) {
    const times = getTime(key);
    const result = times.filter(t => t.chargeable_service_id === csId)[0];
    return result ? result.time : 0;
}
exports.getTimeForCsId = getTimeForCsId;
function clearForPatient(patientId) {
    for (let key in _usersTime) {
        if (!_usersTime.hasOwnProperty(key)) {
            continue;
        }
        const parts = key.split('-');
        if (parts.length < 2) {
            continue;
        }
        if (+parts[1] === patientId) {
            delete _usersTime[key];
        }
    }
}
exports.clearForPatient = clearForPatient;
//# sourceMappingURL=user-time.js.map