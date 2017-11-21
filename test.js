const assert = require('chai').assert
require('./prototypes/array.prototype')
require('./prototypes/date.prototype')

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

const addSeconds = (seconds) => () => (new Date).addSeconds(seconds)

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
    it('should return 3 when addSeconds(3) is passed to interval() for the first time', () => {
        const user = timeTracker.create(key, info)
        assert.equal(user.interval(addSeconds(3)), 3)
    })
    it('interval() should return 3 when addSeconds(3) is passed to setEndTime() for the first time', () => {
        const user = timeTracker.create(key, info)
        assert.equal(user.setEndTime(addSeconds(3)).interval(), 3)
    })
    it('should have cleanup() and interval() values equal', () => {
        const user = timeTracker.create(key, info)
        user.setEndTime(addSeconds(5))
        assert.equal(user.interval(), user.cleanup())
        assert.equal(user.interval(), 5)
        assert.equal(user.cleanup(), 5)
    })
    it('interval() should return 4 when addSeconds(4) is passed to stop() for the first time', () => {
        const user = timeTracker.create(key, info)
        assert.equal(user.stop(addSeconds(4)).interval(), 4)
    })
    it('should have dates.length equal to 1 when stop() is called for the first time', () => {
        const user = timeTracker.create(key, info)
        assert.equal(user.stop().dates.length, 1)
    })
})