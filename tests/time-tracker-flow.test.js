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

    describe('InactivityAction', () => {
        const timeTracker = new TimeTracker()
        const user = timeTracker.get(info)

        describe('User', () => {
            it('should not be in call-mode', () => {
                assert.isFalse(user.callMode)
            })
        })

        it('should neither require modal nor logout (100 seconds)', () => {
            user.inactiveSeconds = 100

            assert.equal(user.inactivityRequiresNoModal(), true)
            assert.equal(user.inactivityRequiresModal(), false)
            assert.equal(user.inactivityRequiresLogout(), false)
        })

        it('should require modal (130 seconds)', () => {
            user.inactiveSeconds = 130

            assert.equal(user.inactivityRequiresNoModal(), false)
            assert.equal(user.inactivityRequiresModal(), true)
            assert.equal(user.inactivityRequiresLogout(), false)
        })

        it('should require logout (610 seconds)', () => {
            user.inactiveSeconds = 610

            assert.equal(user.inactivityRequiresNoModal(), false)
            assert.equal(user.inactivityRequiresModal(), false)
            assert.equal(user.inactivityRequiresLogout(), true)
        })

        describe('CallMode', () => {
            const timeTracker = new TimeTracker()
            const user = timeTracker.get(info)

            user.enter(info, ws)

            user.enterCallMode(info)

            describe('User', () => {
                it('should be in call-mode', () => {
                    assert.isTrue(user.callMode)
                })
            })

            it('should neither require modal nor logout (100 seconds)', () => {
                user.inactiveSeconds = 100
    
                assert.equal(user.inactivityRequiresNoModal(), true)
            })
    
            it('should NOT require modal (130 seconds)', () => {
                user.inactiveSeconds = 130
    
                assert.equal(user.inactivityRequiresModal(), false)
            })
    
            it('should NOT require logout (610 seconds)', () => {
                user.inactiveSeconds = 610
    
                assert.equal(user.inactivityRequiresLogout(), false)
            })
    
            it('should require modal (910 seconds)', () => {
                user.inactiveSeconds = 910
    
                assert.equal(user.inactivityRequiresModal(), true)
            })
    
            it('should require logout (1210)', () => {
                user.inactiveSeconds = 1210
    
                assert.equal(user.inactivityRequiresLogout(), true)
            })
        })
    })
})