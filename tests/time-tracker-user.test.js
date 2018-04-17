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