const axios = require('axios');

let resultsCallback = null;
let isQueued = false;
let queueInterval = 1000;
let queue = {};
let httpRequestCreator = getDefaultHttpRequestCreator();

function getDefaultHttpRequestCreator() {
    return function (url, patients) {
        return axios.post(url, {patients: patients});
    };
}

function setHttpRequestCreator(customHttpRequestCreator) {
    httpRequestCreator = customHttpRequestCreator;
}

function setQueueInterval(value) {
    queueInterval = value;
}

function setResultsCallback(callback) {
    resultsCallback = callback;
}

function getQueue() {
    return queue;
}

function syncPatientTimeWithCPM(syncUrl, patientId, providerId) {
    if (!queue[syncUrl]) {
        queue[syncUrl] = [];
    }

    if (queue[syncUrl].findIndex((x) => x.patientId === patientId && x.providerId === providerId) > -1) {
        return;
    }

    queue[syncUrl].push({patientId, providerId});
    execute();
}

function ignorePatientTimeSync(syncUrl, patientId, providerId) {
    if (!queue[syncUrl]) {
        return;
    }

    const index = queue[syncUrl].findIndex((x) => x.patientId === patientId && x.providerId === providerId);
    if (index > -1) {
        queue[syncUrl].splice(index, 1);
    }
}

function execute() {
    if (isQueued) {
        return;
    }

    isQueued = true;
    setTimeout(syncWithCPM, queueInterval);
}

function syncWithCPM() {

    for (let url in queue) {

        if (!queue.hasOwnProperty(url)) {
            continue;
        }

        const list = queue[url].slice(0);
        if (!list.length) {
            continue;
        }

        syncWithCPMInternal(url, list);
    }

    isQueued = false;
    queue = {};
}

function syncWithCPMInternal(syncUrl, listOfPatients) {

    const listOfPatientIds = listOfPatients.map(x => x.patientId);

    httpRequestCreator(syncUrl, listOfPatientIds)
        .then((response) => {
            if (resultsCallback) {
                resultsCallback(response);
            }
        })
        .catch((err) => {
            console.error(err);
        })
}

module.exports = {
    setResultsCallback,
    syncPatientTimeWithCPM,
    ignorePatientTimeSync,
    getQueue,
    setHttpRequestCreator,
    setQueueInterval
};