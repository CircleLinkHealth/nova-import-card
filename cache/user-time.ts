//copied from StoreTimeTracking.php
const UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];

const _usersTime: UsersTimeCollection = {};

export function storeTime(userId: number, activities: { title: string, is_behavioral: boolean; duration: number }[], totalCcm: number, totalBhi: number) {
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
    _usersTime[userId] = {ccm: finalCcm, bhi: finalBhi};
}

export function getTime(userId: number): TimeEntity {
    if (!_usersTime[userId]) {
        return {ccm: 0, bhi: 0};
    }
    return _usersTime[userId];
}

interface TimeEntity {
    ccm: number;
    bhi: number;
}

interface UsersTimeCollection {
    [userId: number]: TimeEntity;
}
