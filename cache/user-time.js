"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
//copied from StoreTimeTracking.php
var UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];
var _usersTime = {};
function storeTime(activity, userId, ccmTime, bhiTime, replace) {
    if (replace === void 0) { replace = false; }
    if (UNTRACKED_ROUTES.indexOf(activity) > -1) {
        return;
    }
    if (replace) {
        _usersTime[userId] = { ccm: ccmTime, bhi: bhiTime };
        return;
    }
    if (!_usersTime[userId]) {
        _usersTime[userId] = { ccm: 0, bhi: 0 };
    }
    _usersTime[userId].ccm += ccmTime;
    _usersTime[userId].bhi += bhiTime;
}
exports.storeTime = storeTime;
function getTime(userId) {
    if (!_usersTime[userId]) {
        return { ccm: 0, bhi: 0 };
    }
    return _usersTime[userId];
}
exports.getTime = getTime;
