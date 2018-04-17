const {
    assert,
    TimeTracker,
    TimeTrackerUser,
    TimeTrackerInfo,
    WebSocket,
    info,
    key,
    ws,
    addSeconds
} = require('./setup.test')

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