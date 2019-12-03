const {setQueueInterval, setHttpRequestCreator, getQueue, ignorePatientTimeSync, syncPatientTimeWithCPM, setNumberOfSyncAttempts} = require('../sockets/sync.with.cpm');

const {assert} = require('chai');

describe('Sync With CPM', () => {

    const url = 'mock';
    const patientId = 123;
    const providerId = 321;
    const queueInterval = 50;
    const requestResolveTime = 50;

    before(function () {
        setQueueInterval(queueInterval);
        setHttpRequestCreator(() => {
            return new Promise(((resolve, reject) => {
                setTimeout(resolve, requestResolveTime);
            }));
        });
    });

    it('should queue patient id for sync', function (done) {
        syncPatientTimeWithCPM(url, patientId, providerId);
        let queue = getQueue();
        assert.isTrue(queue[url][0].patientId === patientId);
        doneWhenNotExecuting(done);
    });

    it('should queue patient id for sync only once', function (done) {
        syncPatientTimeWithCPM(url, patientId, providerId);
        syncPatientTimeWithCPM(url, patientId, providerId);
        let queue = getQueue();
        assert.isTrue(queue[url].length === 1);
        doneWhenNotExecuting(done);
    });

    it('should remove patient id from sync queue', function (done) {

        let queue = getQueue();

        syncPatientTimeWithCPM(url, patientId, providerId);
        assert.isTrue(queue[url].length === 1);

        ignorePatientTimeSync(url, patientId, providerId);
        assert.isTrue(queue[url].length === 0);

        doneWhenNotExecuting(done);
    });

    it('should sync ccd time two times', function (done) {
        setNumberOfSyncAttempts(2);
        syncPatientTimeWithCPM(url, patientId, providerId);

        checkQueue();
        setTimeout(() => {
            checkQueue();
            doneWhenNotExecuting(done);
        }, queueInterval + requestResolveTime + 5);
    });

    it('should sync ccd time three times', function (done) {
        setNumberOfSyncAttempts(3);
        syncPatientTimeWithCPM(url, patientId, providerId);

        checkQueue();
        setTimeout(() => {
            checkQueue();
            setTimeout(() => {
                checkQueue();
                doneWhenNotExecuting(done);
            }, queueInterval + requestResolveTime + 5);
        }, queueInterval + requestResolveTime + 5);
    });

    after(function (done) {
        ignorePatientTimeSync(url, patientId, providerId);
        doneWhenNotExecuting(done);
    });

    function doneWhenNotExecuting(done) {
        setNumberOfSyncAttempts(1);
        let queue = getQueue();
        if (Object.keys(queue).length) {
            setTimeout(() => doneWhenNotExecuting(done), 50);
        } else {
            setNumberOfSyncAttempts();
            done();
        }
    }

    function checkQueue() {
        let queue = getQueue();
        assert.isTrue(Object.keys(queue).length > 0);
        assert.isTrue(queue[url].length === 1);
    }

});