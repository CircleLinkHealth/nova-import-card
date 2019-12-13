interface TimeEntity {
    ccm: number;
    bhi: number;
}

interface UsersTimeCollection {
    [userId: number]: TimeEntity;
}

const _usersTime: UsersTimeCollection = {};

export function storeTime(userId: number, ccmTime: number, bhiTime: number, replace: boolean = false) {

    if (replace) {
        _usersTime[userId] = {ccm: ccmTime, bhi: bhiTime};
        return;
    }

    if (!_usersTime[userId]) {
        _usersTime[userId] = {ccm: 0, bhi: 0};
    }

    _usersTime[userId].ccm += ccmTime;
    _usersTime[userId].bhi += bhiTime;
}

export function getTime(userId: number): TimeEntity {
    if (!_usersTime[userId]) {
        return {ccm: 0, bhi: 0};
    }
    return _usersTime[userId];
}
