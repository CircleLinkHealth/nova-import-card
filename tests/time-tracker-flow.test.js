const assert = require('chai').assert
require('../prototypes/array.prototype')
require('../prototypes/date.prototype')

const TimeTracker = require('../time-tracker')
const TimeTrackerUser = TimeTracker.TimeTrackerUser

const WebSocket = require('./stubs/ws.stub')

const TimeTrackerInfo = require('./stubs/time-tracker-info.stub')
const info = new TimeTrackerInfo({ totalTime: 0 })
const key = (new TimeTrackerInfo()).createKey()
const ws = new WebSocket()
const activity1 = { name: 'patient-notes-1', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }
const activity2 = { name: 'patient-notes-2', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }
const activity3 = { name: 'patient-notes-3', urlFull: 'http://cpm-web.com/x/y/z', urlShort: '/x/y/z' }

const addSeconds = (seconds) => () => (new Date).addSeconds(seconds)

describe('TimeTrackerFlow', () => {

    const timeTracker = new TimeTracker()
    const user = timeTracker.get(info)

    describe('Enter', () => {
        user.enter(info, ws)

        it('should have totalSeconds as 0', () => {
            assert.equal(user.totalSeconds, 0)
        })

        describe('InactivityModal', () => {
            const timeTracker = new TimeTracker()
            const user = timeTracker.get(info)

            describe('User chose NO', () => {
                user.enter(info, ws)
                user.respondToModal(false)

                it('should have totalSeconds as 30', () => {
                    assert.equal(user.totalSeconds, 30)
                })
            })
            
            describe('User chose YES', () => {
                const timeTracker = new TimeTracker()
                const user = timeTracker.get(info)

                user.enter(info, ws)
                user.inactiveSeconds = 20
                user.respondToModal(true)

                it('should have totalSeconds as 20', () => {
                    assert.equal(user.totalSeconds, 20)
                })
            })
        })
    })
})