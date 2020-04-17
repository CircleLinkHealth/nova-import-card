//copied from StoreTimeTracking.php
const UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];

const _usersTime: UsersTimeCollection = {};

export function storeTime(key: string, activities: { title: string, is_behavioral: boolean; duration: number }[], totalCcm: number, totalBhi: number) {
    let finalCcm = totalCcm;
    let finalBhi = totalBhi;
    activities.forEach(a => {
        if (UNTRACKED_ROUTES.indexOf(a.title) === -1) {
            return;
        }
        if (a.is_behavioral) {
            finalBhi -= a.duration;
        } else {
            finalCcm -= a.duration;
        }
    });
    _usersTime[key] = {ccm: finalCcm, bhi: finalBhi};
}

export function getTime(key: string): TimeEntity {
    if (!_usersTime[key]) {
        return {ccm: 0, bhi: 0};
    }
    return _usersTime[key];
}

interface TimeEntity {
    ccm: number;
    bhi: number;
}

interface UsersTimeCollection {
    [key: string]: TimeEntity;
}
