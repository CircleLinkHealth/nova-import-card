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

function WebSocket() {
    this.active = false
}

const info = (new TimeTrackerInfo())
const key = (new TimeTrackerInfo()).createKey()
const ws = new WebSocket()
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
    const user = timeTracker.get(info)

    it('should make an instance of TimeTrackerUser', () => {
        assert.isNotNull(user)
        assert.isDefined(user)
    })
    it('should have appropriate TimeTrackerUser.prototype', () => {
        assert.isDefined(user.activities)
        assert.isArray(user.activities)

        assert.isDefined(user.inactiveSeconds)
        assert.equal(user.inactiveSeconds, 0)
        
        assert.isDefined(user.key)
        assert.equal(user.key, '3864-344')
    })

    describe('TimeTrackerUser.prototype.enter()', () => {
        const timeTracker = new TimeTracker()
    
        const user = timeTracker.get(info)
    
        it('should have activities.length === 1', () => {
            user.enter(info, ws)
    
            assert.equal(user.activities.length, 1)
        })
        
        it('should have activities array unique', () => {
            user.enter(info, ws)
            user.enter(info, ws)
            user.enter(info, ws)
    
            assert.equal(user.activities.length, 1)
        })
        
        it('should have activities[index].sockets unique', () => {
            user.enter(info, ws)
            user.enter(info, ws)
            user.enter(info, ws)
    
            assert.equal(user.activities[0].sockets.length, 1)
        })
    })

    describe('TimeTrackerUser.prototype.leave()', () => {

        it('should set activities[0].sockets[0] [active] property to false', () => {
            user.enter(info, ws)
            user.leave(ws)
    
            assert.isFalse(ws.active)
            assert.isFalse(user.activities[0].sockets[0].active)
        })
    })
    
    describe('TimeTrackerUser.prototype.exit()', () => {

        it('should set activities[0].sockets[0] [active] property to false', () => {
            user.enter(info, ws)
            user.leave(ws)
    
            assert.isFalse(ws.active)
            assert.isFalse(user.activities[0].sockets[0].active)
        })
    })
    
    describe('TimeTrackerUser.prototype.totalSeconds', () => {

        it('should have totalSeconds set to 0', () => {
            user.enter(info, ws)

            assert.equal(user.totalSeconds, info.totalTime)
        })
    })
    
    describe('TimeTrackerUser.prototype.allSockets', () => {

        it('should have allSockets as an array', () => {
            user.enter(info, ws)

            assert.isArray(user.allSockets)
            assert.equal(user.allSockets.length, 1)
        })
    })
})