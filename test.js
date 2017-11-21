const assert = require('chai').assert

const TimeTracker = require('./time-tracker')
const TimeTrackerUser = TimeTracker.TimeTrackerUser

const TimeTrackerInfo = function () {
    return {
        patientId: "344",
        providerId: "3864",
        totalTime: 339,
        wsUrl: "ws://localhost:3000/time",
        programId: "8",
        urlFull: "https://cpm-web.dev/manage-patients/344/notes",
        urlShort: "/manage-patients/344/notes",
        ipAddr: "127.0.0.1",
        activity: "Notes/Offline Activities Review",
        title: "patient.note.index",
        submitUrl: "https://cpm-web.dev/api/v2.1/pagetimer",
        startTime: "2017-11-21 04:01:10",
        disabled: false,
        createKey() {
            return `${this.patientId}-${this.providerId}`
        }
    }
}

const info = (new TimeTrackerInfo())
const key = (new TimeTrackerInfo()).createKey()

describe('TimeTracker', () => {
    it('should make an instance of TimeTracker', () => {
        const timeTracker = new TimeTracker()

        assert.isNotNull(timeTracker)
        assert.isDefined(timeTracker)
    })
})

describe('TimeTrackerUser', () => {
    const timeTracker = new TimeTracker()
    const user = timeTracker.get(key, info)

    it('should make an instance of TimeTrackerUser', () => {
        assert.isNotNull(user)
        assert.isDefined(user)
    })
    it('should have one item in TimeTrackerUser.prototype.dates', () => {
        assert.isDefined(user.dates)
        assert.isArray(user.dates)
        assert.equal(user.dates.length, 1)
    })
    it('should have null in TimeTrackerUser.prototype.dates.last().end', () => {
        assert.isNull(user.dates.last().end)
    })
    it('should have a Date prototype in TimeTrackerUser.prototype.dates.last().start', () => {
        assert.isDefined(user.dates.last().start)
        assert.isNotNull(user.dates.last().start)
        assert.equal(user.dates.last().start.constructor.name, 'Date')
    })
    it('calling resume() multiple times consecutively should have only ONE (1) effect', () => {
        user.resume().resume().resume()
        assert.isDefined(user.dates)
        assert.isArray(user.dates)
        assert.equal(user.dates.length, 1)
    })
    it('should return 0 when interval() is called for the first time', () => {
        assert.equal(user.interval(), 0)
    })
})