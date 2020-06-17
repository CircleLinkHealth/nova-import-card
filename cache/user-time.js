"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
//copied from StoreTimeTracking.php
//todo: delete these, should not be needed after force_skip
var UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];
var _usersTime = {};
function storeTime(key, activities, totalCcm, totalBhi) {
    var finalCcm = totalCcm;
    var finalBhi = totalBhi;
    activities.forEach(function (a) {
        // remove time for activities that have force_skip or are included in the UNTRACKED_ROUTES array
        if (a.force_skip || UNTRACKED_ROUTES.indexOf(a.title) > -1) {
            console.debug("activity[" + a.title + "] has force_skip, will not store in cache");
            if (a.is_behavioral) {
                finalBhi -= a.duration;
            }
            else {
                finalCcm -= a.duration;
            }
        }
    });
    _usersTime[key] = { ccm: finalCcm, bhi: finalBhi };
}
exports.storeTime = storeTime;
function getTime(key) {
    if (!_usersTime[key]) {
        return { ccm: 0, bhi: 0 };
    }
    return _usersTime[key];
}
exports.getTime = getTime;
