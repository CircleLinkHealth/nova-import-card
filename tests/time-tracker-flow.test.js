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

    describe('Call Mode', () => {
        const timeTracker = new TimeTracker()
        const info1 = { ...info, ...{ patientFamilyId: 1 } }
        const info2 = { ...info, ...{ patientId: 5, patientFamilyId: 2 } }
        const user1 = timeTracker.get(info1)
        const user2 = timeTracker.get(info2)

        describe('Test Users', () => {
            it('should have same practitioner ID', () => {
                assert.equal(info1.providerId, info2.providerId)
            })
            it('should have different patientIDs', () => {
                assert.notEqual(info1.patientId, info2.patientId)
            })
            it('should have different patientFamilyIDs', () => {
                assert.notEqual(info1.patientFamilyId, info2.patientFamilyId)
            })
        })
        
        describe('Exits If User With Different Family ID Is Entered', () => {
            user1.enter(info1, ws)
            user1.enterCallMode(info1, ws)

            assert.isTrue(user1.callMode)
            assert.isFalse(user2.callMode)

            user2.enter(info2, ws)

            assert.isFalse(user1.callMode)

            it('should pass', () => {

            })
        })
    })
        
    describe('Does NOT Exit If Both Users Have NULL Patient Family IDs', () => {

        const timeTracker = new TimeTracker()
        const info1 = { ...info, ...{ patientFamilyId: null } }
        const info2 = { ...info, ...{ patientId: '23', patientFamilyId: null } }
        const user1 = timeTracker.get(info1)
        const user2 = timeTracker.get(info2)

        user1.enter(info1, ws)
        user1.enterCallMode(info1, ws)

        assert.isTrue(user1.callMode)
        assert.isFalse(user2.callMode)

        user2.enter(info2, ws)

        assert.isTrue(user1.callMode)

        it('should pass', () => {

        })
    })
        
    describe('should Exit If One User Has Patient Family IDs value', () => {

        const timeTracker = new TimeTracker()
        const info1 = { ...info, ...{ patientFamilyId: 1 } }
        const info2 = { ...info, ...{ patientId: '23', patientFamilyId: null } }
        const user1 = timeTracker.get(info1)
        const user2 = timeTracker.get(info2)

        user1.enter(info1, ws)
        user1.enterCallMode(info1, ws)

        assert.isTrue(user1.callMode)
        assert.isFalse(user2.callMode)

        user2.enter(info2, ws)

        assert.isFalse(user1.callMode)

        it('should pass', () => {

        })
    })
        
    describe('should Exit If One User Has NULL Patient ID', () => {

        const timeTracker = new TimeTracker()
        const info1 = { ...info }
        const info2 = { ...info, ...{ patientId: null } }
        const user1 = timeTracker.get(info1)
        const user2 = timeTracker.get(info2)

        user1.enter(info1, ws)
        user1.enterCallMode(info1, ws)

        assert.isDefined(info1.patientId)
        assert.isNull(info2.patientId)

        assert.isTrue(user1.callMode)
        assert.isFalse(user2.callMode)

        user2.enter(info2, ws)

        assert.isFalse(user1.callMode)

        it('should pass', () => {

        })
    })
        
    describe('should Exit If One User Has "0" Patient ID', () => {

        const timeTracker = new TimeTracker()
        const info1 = { ...info, ...{ patientId: 1 } }
        const info2 = { ...info, ...{ patientId: '0' } }
        const user1 = timeTracker.get(info1)
        const user2 = timeTracker.get(info2)

        user1.enter(info1, ws)
        user1.enterCallMode(info1, ws)

        assert.isDefined(info1.patientId)
        assert.equal(info2.patientId, '0')

        assert.isTrue(user1.callMode)
        assert.isFalse(user2.callMode)

        user2.enter(info2, ws)

        assert.isFalse(user1.callMode)

        it('should pass', () => {

        })
    })
})