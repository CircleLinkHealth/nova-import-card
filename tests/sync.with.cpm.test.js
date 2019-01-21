const {setQueueInterval,setHttpRequestCreator, getQueue, ignorePatientTimeSync, syncPatientTimeWithCPM} = require('../sockets/sync.with.cpm');

const {assert} = require('chai');

describe('Sync With CPM', () => {

    let url = 'mock';
    let patientId = 123;
    let providerId = 321;

    before(function () {
        setQueueInterval(50);
        setHttpRequestCreator(() => {
            return new Promise(((resolve, reject) => {
                setTimeout(resolve, 50);
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

    after(function (done) {
        ignorePatientTimeSync(url, patientId, providerId);
        doneWhenNotExecuting(done);
    });

    function doneWhenNotExecuting(done) {
        let queue = getQueue();
        if (Object.keys(queue).length) {
            setTimeout(() => doneWhenNotExecuting(done), 50);
        }
        else {
            done();
        }
    }

});