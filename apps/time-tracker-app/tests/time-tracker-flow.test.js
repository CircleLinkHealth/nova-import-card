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
                user.respondToModal(false, info)

                it('should have totalSeconds as 30', () => {
                    assert.equal(user.totalSeconds, 30)
                })
            })

            describe('User chose YES', () => {
                const timeTracker = new TimeTracker()
                const user = timeTracker.get(info)

                user.enter(info, ws)
                user.inactiveSeconds = 20
                user.respondToModal(true, info)

                it('should have totalSeconds as 20', () => {
                    assert.equal(user.totalSeconds, 20)
                })
            })
        })
    })

    describe('UserStart', () => {
        describe('PageLoadTime', () => {
            it('should add to activity duration', () => {
                const timeTracker = new TimeTracker()
                const info = new TimeTrackerInfo({ initSeconds: 5 })
                const user = timeTracker.get(info)

                user.start(info, ws)

                assert.equal(user.totalDuration, 5)
            })
        })

        describe('UserInCallMode', () => {
            const timeTracker = new TimeTracker()
            const info = new TimeTrackerInfo({ initSeconds: 5 })
            const user = timeTracker.get(info)

            user.callMode = true

            user.start(info, ws)

            // assert.isTrue(user.allSockets[0].messages.some(data => {
            //     return JSON.parse(data).message === 'server:call-mode:enter'
            // }))
        })
    })

    describe('UserEnter', () => {
        describe('NoModalRequired', () => {
            it('should have total duration equal to 100', () => {
                const timeTracker = new TimeTracker()
                const user = timeTracker.get(info)

                user.start(info, ws)

                user.inactiveSeconds = 100

                user.enter(info, ws)

                assert.equal(user.totalDuration, 100)
            })
        })

        describe('Modal Required', () => {
            it('should trigger inactive modal', () => {
                const timeTracker = new TimeTracker()
                const user = timeTracker.get(info)

                user.start(info, ws)

                user.inactiveSeconds = 120

                user.enter(info, ws)

                assert.equal(user.totalDuration, 0)

                assert.equal(JSON.parse(user.allSockets[0].messages.slice(-1)[0]).message, 'server:modal')
            })
        })

        describe('Logout', () => {
            it('should logout if inactivity-seconds is more than 600', () => {
                const timeTracker = new TimeTracker()
                const user = timeTracker.get(info)

                user.start(info, ws)

                user.inactiveSeconds = 601

                user.enter(info, ws)

                assert.equal(JSON.parse(user.allSockets[0].messages.slice(-1)[0]).message, 'server:logout')
            })

            describe('ClientInactivity', () => {
                it('should not reset inactive duration more than once', () => {
                    const timeTracker = new TimeTracker()
                    const user = timeTracker.get(info)

                    user.start(info, ws)

                    user.activities[0].duration = 125

                    user.inactiveSeconds = 601

                    user.clientInactivityLogout(info)

                    user.clientInactivityLogout(info)

                    assert.equal(user.totalDuration, 35)
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
                user.inactiveSeconds = user.ALERT_TIMEOUT_CALL_MODE + 10;

                assert.equal(user.inactivityRequiresModal(), true)
            })

            it('should require logout (1210)', () => {
                user.inactiveSeconds = user.LOGOUT_TIMEOUT_CALL_MODE + 10;

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

    describe('BHI', () => {

        const timeTracker = new TimeTracker()

        const info1 = { ...info, ...{ patientId: 1, chargeableServiceId: 2 } }
        const info2 = { ...info, ...{ patientId: 1, chargeableServiceId: 1 } }

        const user = timeTracker.get(info1)

        it('should have different durations', () => {
            user.start(info1, ws)

            let activity1 = user.findActivity(info1)

            activity1.duration += 30

            user.enter(info2, ws)

            let activity2 = user.findActivity(info2)

            assert.notEqual(activity1, activity2)

            assert.equal(user.getTotalSecondsForCsId(2), 30)

            assert.equal(user.getTotalSecondsForCsId(1), 0)

            assert.equal(user.totalSeconds, 30)
        })
    })

    describe('Activity Start Time', () => {

        const startTime1 = '2017-11-21 04:01:10';
        const startTime2 = '2017-11-21 06:01:10';

        const timeTracker = new TimeTracker()
        const info1 = { ...info, ...{ patientId: 1, chargeableServiceId: 1, startTime: startTime1 } };
        const info2 = { ...info, ...{ patientId: 1, chargeableServiceId: 1, startTime: startTime2 } };
        const user = timeTracker.get(info1);

        it('should set new start time of activity with new websocket connection', () => {
            user.start(info1, ws);
            const activity = user.findActivity(info1);
            activity.duration += 30;

            const originalStartTime = activity.start_time;
            assert.equal(originalStartTime, startTime1);

            user.exit();
            user.close();

            user.start(info2, ws);
            const sameActivity = user.findActivity(info2);
            sameActivity.duration += 40;

            assert.equal(sameActivity.start_time, startTime2);
            assert.notEqual(originalStartTime, sameActivity.start_time);
        });

        it('should keep same start time of activity if no new websocket connection', () => {
            user.start(info1, ws);
            const activity = user.findActivity(info1);
            activity.duration += 30;

            const originalStartTime = activity.start_time;
            assert.equal(originalStartTime, startTime1);

            user.start(info2, ws);
            const sameActivity = user.findActivity(info2);
            sameActivity.duration += 40;

            assert.equal(sameActivity.start_time, startTime1);
            assert.notEqual(sameActivity.start_time, startTime2);
            assert.equal(originalStartTime, sameActivity.start_time);
        });
    });
})
