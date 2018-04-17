const assert = require('chai').assert
require('../prototypes/array.prototype')
require('../prototypes/date.prototype')

const TimeTracker = require('../time-tracker')
const TimeTrackerUser = TimeTracker.TimeTrackerUser

const WebSocket = require('./stubs/ws.stub')

const TimeTrackerInfo = require('./stubs/time-tracker-info.stub')
const info = new TimeTrackerInfo()
const key = (new TimeTrackerInfo()).createKey()
const ws = new WebSocket()
const activity1 = { name: 'patient-notes-1', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }
const activity2 = { name: 'patient-notes-2', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }
const activity3 = { name: 'patient-notes-3', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }

const addSeconds = (seconds) => () => (new Date).addSeconds(seconds)

describe('TimeTrackerFlow', () => {

    const timeTracker = new TimeTracker()
    const user = timeTracker.get(info)


    it('should return 3 when addSeconds(3) is passed to interval() for the first time', () => {
        assert.equal(user.interval(addSeconds(3)), 3)
    })

    it('interval() should return 3 when addSeconds(3) is passed to setEndTime() for the first time', () => {
        assert.equal(user.setEndTime(addSeconds(3)).interval(), 3)
    })

    it('interval() should return 4 when addSeconds(4) is passed to stop() for the first time', () => {
        assert.equal(user.stop(addSeconds(4)).interval(), 4)
    })

    it('should have dates.length equal to 1 when stop() is called for the first time', () => {
        assert.equal(user.stop().dates.length, 1)
    })
})