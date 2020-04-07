"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
//copied from StoreTimeTracking.php
var UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];
var _usersTime = {};
function storeTime(userId, activities, totalCcm, totalBhi) {
    var finalCcm = totalCcm;
    var finalBhi = totalBhi;
    activities.forEach(function (a) {
        if (UNTRACKED_ROUTES.indexOf(a.title) === -1) {
            return;
        }
        if (a.is_behavioral) {
            finalBhi -= a.duration;
        }
        else {
            finalCcm -= a.duration;
        }
    });
    _usersTime[userId] = { ccm: finalCcm, bhi: finalBhi };
}
exports.storeTime = storeTime;
function getTime(userId) {
    if (!_usersTime[userId]) {
        return { ccm: 0, bhi: 0 };
    }
    return _usersTime[userId];
}
exports.getTime = getTime;
