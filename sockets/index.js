const colors = require('../logger/colors')
const {setResultsCallback, syncPatientTimeWithCPM, ignorePatientTimeSync} = require('./sync.with.cpm');
const errorLogger = require('../logger').getErrorLogger();
const storeTime = require("../cache/user-time").storeTime;

module.exports = app => {
    require('express-ws')(app);

    const TimeTracker = require('../time-tracker')
    const TimeTrackerUser = TimeTracker.TimeTrackerUser

    const axios = require('axios')
    const axiosRetry = require('axios-retry');
    axiosRetry(axios, {retries: 3, retryDelay: axiosRetry.exponentialDelay});

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

            const timeOnCpm = times[userId];
            for (let j = 0; j < users.length; j++) {
                const user = users[j];
                if (user.patientId !== userId) {
                    continue;
                }

                let shouldSync = false;

                //set values from cpm
                if (user.totalCCMTime < timeOnCpm.ccm_time) {
                    shouldSync = true;
                    user.totalCCMTime = timeOnCpm.ccm_time;
                }

                if (user.totalBHITime < timeOnCpm.bhi_time) {
                    shouldSync = true;
                    user.totalBHITime = timeOnCpm.bhi_time;
                }

                const syncTime = timeOnCpm.ccm_time + timeOnCpm.bhi_time;
                if (!shouldSync) {
                    console.log(`sync time [${syncTime}] is equal or less to current time [${user.totalTime}]. ignoring`);
                    continue;
                }

                user.totalTime = user.totalCCMTime + user.totalBHITime;
                const cachedTime = user.totalCcmTimeFromCache + user.totalBhiTimeFromCache;

                console.log('sync time[', syncTime, '] cachedTime[', cachedTime, ']', 'now[', user.totalTime, ']');
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
                if (data.constructor.name === 'Object') {
                    if (data.info || data.message === 'PING') {
                        if (data.message === 'client:start') {
                            try {
                                const info = data.info;
                                const user = app.getTimeTracker(info).get(info);
                                user.start(info, ws);
                                user.sync();

                                //sync the first time
                                if (user.allSockets.length === 1 && !info['noLiveCount'] && info['timeSyncUrl'] && +info['patientId'] > 0) {
                                    syncPatientTimeWithCPM(info['timeSyncUrl'], info['patientId'])
                                }
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:leave') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.leave(ws)
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (['client:enter', 'client:bhi'].includes(data.message)) {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.closeOtherBehavioralActivity(info, ws)
                                user.enter(info, ws)
                                user.sync()
                                if (data.message === 'client:bhi') {
                                    user.switchBhi(info)
                                }
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:modal') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.respondToModal(!!data.response, info)

                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:inactive-modal:show') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.showInactiveModal(info)
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:inactive-modal:close') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.closeInactiveModal(info, !!data.response)
                                user.sync()
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:call-mode:enter') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.enterCallMode(info)
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:call-mode:exit') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.exitCallMode(info)
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:timeouts:override') {
                            try {
                                const info = data.info
                                const timeouts = data.timeouts || {}
                                const user = app.getTimeTracker(info).get(info)
                                user.overrideTimeouts(timeouts)
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'client:logout') {
                            try {
                                const info = data.info
                                const user = app.getTimeTracker(info).get(info)
                                user.clientInactivityLogout(info)
                            } catch (ex) {
                                errorThrow(ex, ws)
                                return;
                            }
                        } else if (data.message === 'PING') {

                        } else {
                            errorThrow(new Error('invalid message'), ws)
                        }
                    } else {
                        errorThrow('[data.info] must be a valid Object', ws);
                    }
                } else {
                    errorThrow('[data] must be a valid Object', ws);
                }
            } catch (ex) {
                errorThrow(ex, ws);
            }
        });

        ws.on('close', ev => {
            const keyInfo = {patientId: ws.patientId, providerId: ws.providerId};

            // there are cases where we have same keyInfo in timeTracker and timeTrackerNoLiveCount.
            // for this reason, we should check both collections
            // const user = timeTracker.exists(keyInfo) ? timeTracker.get(keyInfo) :
            //    (timeTrackerNoLiveCount.exists(keyInfo) ? timeTrackerNoLiveCount.get(keyInfo) : null)

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
        user.exit(ws)
        if (user.allSockets.length === 0) {
            //no active sessions
            const url = user.url

            if (user.timeSyncUrl) {
                ignorePatientTimeSync(user.timeSyncUrl, user.patientId);
            }

            const requestData = {
                patientId: user.patientId,
                providerId: user.providerId,
                ipAddr: user.ipAddr,
                programId: user.programId,
                activities: user.activities.filter(activity => activity.duration > 0).map(activity => ({
                    name: activity.name,
                    title: activity.title,
                    duration: activity.duration,
                    url: activity.url,
                    url_short: activity.url_short,
                    start_time: activity.start_time,
                    is_behavioral: activity.isBehavioral
                }))
            };

            if (user.totalCcmSeconds === 0 && user.totalBhiSeconds === 0) {
                console.log('will not cache ccc because time is 0');
            } else {
                console.log('caching ccm', user.totalCcmSeconds);
                requestData.activities.forEach((activity) => {
                    storeTime(activity.title,
                        requestData.patientId,
                        activity.is_behavioral ? 0 : activity.duration,
                        activity.is_behavioral ? activity.duration : 0,
                        false);
                });
            }

            axios.post(url, requestData).then((response) => {
                console.log(response.status, response.data, requestData.patientId, requestData.activities.map(activity => activity.duration).join(', '))
            }).catch((err) => {
                errorLogger.report(err);
                console.error(err)
            })

            $emitter.emit('socket:server:logout', requestData)

            user.close()
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
                console.log(
                    'key:', user.key,
                    'activities:', user.activities.filter(activity => activity.isActive).length,
                    'ccm:', user.totalCcmSeconds,
                    'bhi:', user.totalBhiSeconds,
                    'inactive-seconds:', user.inactiveSeconds,
                    'durations:', user.activities.map(activity => (activity.isActive ? colors.FgGreen : colors.FgRed) + activity.duration + colors.Reset).join(', '),
                    'sockets:', user.allSockets.length,
                    'call-mode:', (user.callMode ? colors.FgBlue : '') + user.callMode + colors.Reset
                )
            }
        }
    }, 1000);

};

/**
 * restart process every day at 2 am
 */

const THRESHOLD_INTERVAL_SECONDS = 60 * 2; //2 minutes
const HOURS = 2;
const MINUTES = 0;
setInterval(function () {

    const upTimeSeconds = Math.floor(process.uptime());

    if (upTimeSeconds < THRESHOLD_INTERVAL_SECONDS) {
        // uptime less than 2 minutes.
        // most probably process was just restarted
        // console.debug("Uptime is ", upTimeSeconds, "seconds. Exiting.");
        return;
    }

    const dateNow = new Date();
    const hours = dateNow.getHours();
    const minutes = dateNow.getMinutes();
    // console.debug('Hours are now', hours, 'and minutes', minutes);

    if (hours === HOURS && minutes === MINUTES) {
        // console.debug('Exiting. Please restart me PM.');
        process.exit(0);
    }

}, 1000 * 60); //check every minute
