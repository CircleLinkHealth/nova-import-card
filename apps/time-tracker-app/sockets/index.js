const colors = require('../logger/colors')
const {setResultsCallback, syncPatientTimeWithCPM} = require('./sync.with.cpm');

module.exports = app => {
    require('express-ws')(app);

    const TimeTracker = require('../time-tracker')
    const $emitter = require('./sockets.events')

    const timeTracker = new TimeTracker($emitter)
    const timeTrackerNoLiveCount = new TimeTracker($emitter)

    app.timeTracker = timeTracker
    app.timeTrackerNoLiveCount = timeTrackerNoLiveCount
    app.getTimeTracker = (info) => {
        if (info && info.noLiveCount) return timeTrackerNoLiveCount
        else return timeTracker
    }

    const wsErrorHandler = function (err) {
        if (err) console.error("ws-error", err)
    }

    const errorThrow = (err, ws) => {
        console.error(err)
        if (ws) {
            if (ws.readyState === ws.OPEN) {
                ws.send(JSON.stringify({
                    error: err
                }), wsErrorHandler)
            }
        }
    }


    const syncPatientTimes = (cpmResp) => {

        if (cpmResp.status !== 200) {
            console.error('there was an error syncing patient times');
            return;
        }

        const times = cpmResp.data;
        const users = app.getTimeTracker().users();

        for (let userId in times) {

            if (!times.hasOwnProperty(userId)) {
                continue;
            }

            /**
             * [
             *  { chargeable_service_id, time }
             * ]
             */
            const timeOnCpm = times[userId];
            for (let j = 0; j < users.length; j++) {
                const user = users[j];
                if (user.patientId !== userId) {
                    continue;
                }

                const toSet = [];
                for (let k = 0; k < timeOnCpm.length; k++) {
                    const csTimeFromCpm = timeOnCpm[k];
                    const csTimeHere = user.getTotalTimeForCsId(csTimeFromCpm.chargeable_service_id);
                    if (csTimeHere < csTimeFromCpm.time) {
                        toSet.push({
                            chargeable_service: {id: csTimeFromCpm.chargeable_service_id},
                            total_time: csTimeFromCpm.time
                        });
                    }
                }

                const syncTime = timeOnCpm.reduce((a, b) => a + b.time, 0);
                if (!toSet.length) {
                    console.log(`sync time [${syncTime}] is equal or less to current time [${user.totalTime}]. ignoring`);
                    continue;
                }

                user.setChargeableServices({chargeableServices: toSet});

                console.log('sync time[', syncTime, '] cachedTime[', user.totalTimeFromCache, ']', 'now[', user.totalTime, ']');
                user.sync();
            }
        }


    };
    setResultsCallback(syncPatientTimes);

    app.ws('/events', (ws, req) => {
        try {
            $emitter.emit('socket:add', ws)

            ws.on('close', () => {
                $emitter.emit('socket:remove', ws)
            })
        } catch (ex) {
            errorThrow(ex, ws);
        }
    })

    app.ws('/time', (ws, req) => {
        ws.on('message', (message = '') => {
            try {
                const data = JSON.parse(message);
                /**
                 * Sample structure of [data] object
                 * {
                 *  'message': 'client:start'
                 *  'info': { ... }
                 * }
                 */

                if (data.constructor.name !== 'Object') {
                    errorThrow('[data] must be a valid Object', ws);
                    return;
                }

                if (data.message === 'PING') {
                    //should reply with PONG
                    return;
                }

                if (!data.info) {
                    errorThrow('[data.info] must be a valid Object', ws);
                    return;
                }

                const info = data.info;
                const user = app.getTimeTracker(info).get(info);

                switch (data.message) {
                    case 'client:start':
                        user.start(info, ws);
                        user.sync();

                        //sync the first time
                        if (user.allSockets.length === 1 && !info['noLiveCount'] && info['timeSyncUrl'] && +info['patientId'] > 0) {
                            syncPatientTimeWithCPM(info['timeSyncUrl'], info['patientId'])
                        }
                        break;

                    case 'client:leave':
                        user.leave(ws);
                        break;

                    case 'client:enter':
                    case 'client:chargeable-service-change':
                        user.closeOtherSameActivityWithOtherChargeableServiceId(info, ws);
                        user.enter(info, ws);
                        user.sync();
                        if (data.message === 'client:chargeable-service-change') {
                            user.changeChargeableService(info);
                        }
                        break;

                    case 'client:modal':
                        user.respondToModal(!!data.response, info);
                        break;

                    case 'client:inactive-modal:show':
                        user.showInactiveModal(info);
                        break;

                    case 'client:inactive-modal:close':
                        user.closeInactiveModal(info, !!data.response);
                        user.sync();
                        break;

                    case 'client:call-mode:enter':
                        user.enterCallMode(info);
                        break;

                    case 'client:call-mode:exit':
                        user.exitCallMode();
                        break;

                    case 'client:timeouts:override':
                        const timeouts = data.timeouts || {};
                        user.overrideTimeouts(timeouts);
                        break;

                    case 'client:logout':
                        user.clientInactivityLogout(info);
                        break;

                    case 'client:activity':
                        user.changeActivity(data.info, ws);
                        break;

                    default:
                        errorThrow(new Error('invalid message'), ws);
                        break;
                }
            } catch (ex) {
                errorThrow(ex, ws);
            }
        });

        ws.on('close', ev => {
            const keyInfo = {patientId: ws.patientId, providerId: ws.providerId, isFromCaPanel: ws.isFromCaPanel};

            // there are cases where we have same keyInfo in timeTracker and timeTrackerNoLiveCount.
            // for this reason, we should check both collections

            const user = timeTracker.exists(keyInfo) ? timeTracker.get(keyInfo) : null;
            if (user) {
                closeSessionAndPostToCPM(user, ws);
            }

            const userNoLiveCount = timeTrackerNoLiveCount.exists(keyInfo) ? timeTrackerNoLiveCount.get(keyInfo) : null;
            if (userNoLiveCount) {
                closeSessionAndPostToCPM(userNoLiveCount, ws);
            }
        });
    });

    function closeSessionAndPostToCPM(user, ws) {
        user.exit(ws);
        if (user.allSockets.length === 0) {
            user.sendToCpm(true);
            user.close();
        }
    }

    setInterval(() => {
        for (const user of [...timeTracker.users(), ...timeTrackerNoLiveCount.users()]) {
            user.activities.forEach(activity => {
                if (activity.isActive && !activity.isInActiveModalShown) {
                    activity.duration += 1;
                }
            })

            //CPM-1024 - non-ccm pages are missing time
            if (/*!user.noLiveCount && */user.allSockets.length > 0 && user.allSockets.every(ws => !ws.active)) {
                user.inactiveSeconds += 1
            }

            //CPM-1024 - non-ccm pages are missing time
            if (/*!user.noLiveCount && */process.env.NODE_ENV !== 'production') {
                const obj = {
                    'noLiveCount': user.noLiveCount ? 'Yes' : 'No',
                    'key': user.key,
                    'activities': user.activities
                        .map(a => {
                            return `Name[${a.name}] - Active[${a.isActive}] - Seconds[${a.duration}] - CsId[${a.chargeableServiceId}]`;
                        })
                        .join(' | '),
                    'totalTime': user.totalTime,
                    'totalDuration': user.totalDuration,
                    'inactive-seconds': user.inactiveSeconds,
                    'sockets': user.allSockets.length,
                    'call-mode': user.callMode ? 'Yes' : 'No'
                }
                console.table(obj);
            }
        }
    }, 1000);
};
