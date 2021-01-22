//copied from StoreTimeTracking.php
//todo: delete these, should not be needed after force_skip
import {TimeEntity, TimeTrackerInfo, UsersTimeCollection} from "../types";

const UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];

const _usersTime: UsersTimeCollection = {};

export function getCacheKey(info: TimeTrackerInfo): string {
    if (info.isFromCaPanel) {
        return `${info.providerId}`;
    } else {
        return `${info.providerId}-${info.patientId}`;
    }
}

export function storeTime(key: string, activities: { title: string, chargeable_service_id: number; force_skip: boolean; duration: number }[], times: TimeEntity[]) {
    const removeTimeFromCs = (csId: number, timeToRemove: number) => {
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

export function getAll() {
    return _usersTime;
}

export function getTime(key: string): TimeEntity[] {
    if (!_usersTime[key]) {
        return [];
    }
    return _usersTime[key];
}

export function getTimeForCsId(key: string, csId: number): number {
    const times = getTime(key);
    const result = times.filter(t => t.chargeable_service_id === csId)[0];
    return result ? result.time : 0;
}

export function clearForPatient(patientId: number) {
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
