//copied from StoreTimeTracking.php
//todo: delete these, should not be needed after force_skip
const UNTRACKED_ROUTES = [
    'patient.activity.create',
    'patient.activity.providerUIIndex',
    'patient.reports.progress',
];

const _usersTime: UsersTimeCollection = {};

export function storeTime(key: string, activities: { title: string, is_behavioral: boolean; force_skip: boolean; duration: number }[], totalCcm: number, totalBhi: number) {
    let finalCcm = totalCcm;
    let finalBhi = totalBhi;
    activities.forEach(a => {
        // remove time for activities that have force_skip or are included in the UNTRACKED_ROUTES array
        if (a.force_skip || UNTRACKED_ROUTES.indexOf(a.title) > -1) {
            console.debug(`activity[${a.title}] has force_skip, will not store in cache`);
            if (a.is_behavioral) {
                finalBhi -= a.duration;
            } else {
                finalCcm -= a.duration;
            }
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
