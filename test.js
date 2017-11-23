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
const activity1 = { name: 'patient-notes-1', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }
const activity2 = { name: 'patient-notes-2', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }
const activity3 = { name: 'patient-notes-3', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }

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
        assert.equal(user.stop(activity1, addSeconds(4)).interval(), 4)
    })
    it('should have dates.length equal to 1 when stop() is called for the first time', () => {
        const user = timeTracker.create(key, info)
        assert.equal(user.stop(activity1).dates.length, 1)
    })
})

describe('TimeTracker Flow', () => {
    const timeTracker = new TimeTracker()

    it('resume()->2->stop()->2->resume()->8->stop() should have interval() equal to 10 and dates.length equal to 2', () => {
        const user = timeTracker.create(key, info) //resume() is implicit
        user.stop(activity1, addSeconds(2)) //add 2 seconds
        user.resume(addSeconds(4)) //resume after 2 seconds
        user.stop(activity1, addSeconds(12)) //lasts for 8 seconds
        assert.equal(user.interval(), 10)
        assert.equal(user.dates.length, 2)
    })
})

describe('TimeTracker Activity Flow', () => {
    const timeTracker = new TimeTracker()
    
    it('should have activity-time set to 3 seconds after stopping', () => { 
        const user = timeTracker.create(key, info)
        user.stop(activity1, addSeconds(3))
        assert.equal(user.info.activities.find(a => a.name === activity1.name).duration, 3)
    })
    it('should have activity-time set to 5 seconds after stopping', () => {
        const user = timeTracker.create(key, info)
        user.stop(activity1, addSeconds(3))
        user.cleanup()
        user.resume(addSeconds(4))
        user.stop(activity1, addSeconds(6))
        assert.equal(user.info.activities.find(a => a.name === activity1.name).duration, 5)
    })
    it('should have activity-time set to 5 seconds for activity1 and 10 seconds for activity2 after stopping', () => {
        const user = timeTracker.create(key, info)
        user.stop(activity1, addSeconds(3)) //activity1 lasts for 3 seconds
        user.cleanup()
        user.resume(addSeconds(4))
        user.stop(activity2, addSeconds(11)) //activity2 lasts for 7 seconds
        user.cleanup()
        user.resume(addSeconds(12))
        user.stop(activity1, addSeconds(14)) //activity1 lasts for 2 seconds
        user.cleanup()
        user.resume(addSeconds(15))
        user.stop(activity2, addSeconds(18)) //activity2 lasts for 3 seconds
        console.log(user.info.activities)
        assert.equal(user.info.activities.find(a => a.name === activity1.name).duration, 5)
        assert.equal(user.info.activities.find(a => a.name === activity2.name).duration, 10)
    })
})