const {EventEmitter} = require('events')
const {validateInfo, createActivity, formatTimeForServer} = require('./utils.fn');

const axios = require('axios')
const axiosRetry = require('axios-retry');
axiosRetry(axios, {retries: 3, retryDelay: axiosRetry.exponentialDelay});

const errorLogger = require('../logger').getErrorLogger();
const storeTime = require("../cache/user-time").storeTime;
const getTime = require("../cache/user-time").getTime;

const {ignorePatientTimeSync} = require('../sockets/sync.with.cpm');

function TimeTrackerUser(info, $emitter = new EventEmitter()) {

    validateInfo(info)

    let key;
    if (info.isFromCaPanel) {
        key = `${info.providerId}`;
    } else {
        key = `${info.providerId}-${info.patientId}`;
    }

    const validateWebSocket = (ws) => {
        if (!ws) throw new Error('[ws] must be a valid WebSocket instance')
    }

    const user = {
        key: key,
        inactiveSeconds: 0, //inactive time in seconds
        activities: [],
        patientId: info.patientId,
        providerId: info.providerId,
        url: info.submitUrl,
        timeSyncUrl: info.timeSyncUrl,
        programId: info.programId,
        ipAddr: info.ipAddr,
        totalTime: (Number(info.totalTime) || 0),
        totalCCMTime: (Number(info.totalCCMTime) || 0),
        totalBHITime: (Number(info.totalBHITime) || 0),
        noLiveCount: info.noLiveCount,
        patientFamilyId: info.patientFamilyId,
        isLoggingOut: null,
        get ccmDuration() {
            return this.activities.filter(activity => !activity.isBehavioral).reduce((a, b) => a + b.duration, 0)
        },

        get bhiDuration() {
            return this.activities.filter(activity => !!activity.isBehavioral).reduce((a, b) => a + b.duration, 0)
        },

        get totalCcmSeconds() {
            return this.ccmDuration + this.totalCCMTime
        },

        get totalBhiSeconds() {
            return this.bhiDuration + this.totalBHITime
        },

        get totalCcmTimeFromCache() {
            return getTime(this.key).ccm;
        },

        get totalCcmSecondsFromCache() {
            return this.ccmDuration + this.totalCcmTimeFromCache;
        },

        get totalBhiTimeFromCache() {
            return getTime(this.key).bhi;
        },

        get totalBhiSecondsFromCache() {
            return this.bhiDuration + this.totalBhiTimeFromCache;
        },

        /**
         * @returns {Number} total duration in seconds of activities excluding initial-total-time
         */
        get totalDuration() {
            return this.ccmDuration + this.bhiDuration
        },
        /**
         * @returns {Number} total duration in seconds of activities plus initial-total-time
         */
        get totalSeconds() {
            return this.totalBhiSeconds + this.totalCcmSeconds
        },

        /**
         * @returns {Array} list of all sockets in all activities belongs to this user
         */
        get allSockets() {
            return this.activities.map(activity => activity.sockets).reduce((a, b) => a.concat(b), [])
        },
        /**
         * @returns {Boolean} whether or not a call is being made
         */
        get callMode() {
            return this.activities.reduce((a, b) => (a || b.callMode), false)
        },
        /**
         * @returns {Boolean} whether this user has a BHI activity
         */
        isBehavioral: false,
        /**
         *
         * @param {any} data JSON or string you want to send via web sockets
         * @param {*} socket WebSocket instance you want to exclude from broadcast
         */
        broadcast(data, socket) {
            this.allSockets.forEach(ws => {
                const shouldSend = socket ? (socket !== ws) : true // if socket arg is specified, don't send to that socket
                if (ws.readyState === ws.OPEN && shouldSend) {
                    ws.send(JSON.stringify(data))
                }
            })
        },
        inactivityRequiresNoModal() {
            return this.inactiveSeconds < (!this.callMode ? this.ALERT_TIMEOUT : this.ALERT_TIMEOUT_CALL_MODE) // 2 minutes if !call-mode and 15 minutes if in call-mode (120, 900)
        },
        inactivityRequiresModal() {
            return !this.inactivityRequiresNoModal() && this.inactiveSeconds < (!this.callMode ? this.LOGOUT_TIMEOUT : this.LOGOUT_TIMEOUT_CALL_MODE) // 10 minutes if !call-mode and 20 minutes if in call-mode (600, 1200)
        },
        inactivityRequiresLogout() {
            return !this.inactivityRequiresModal() && !this.inactivityRequiresNoModal()
        },
        MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS: 30,
        ALERT_TIMEOUT: 120,
        LOGOUT_TIMEOUT: 600,
        ALERT_TIMEOUT_CALL_MODE: 1800, //30 minutes
        LOGOUT_TIMEOUT_CALL_MODE: 3600, //60 minutes
        overrideTimeouts(options = {}) {
            this.ALERT_TIMEOUT = Math.ceil(options.alertTimeout) || this.ALERT_TIMEOUT;
            this.LOGOUT_TIMEOUT = Math.ceil(options.logoutTimeout) || this.LOGOUT_TIMEOUT;
            this.ALERT_TIMEOUT_CALL_MODE = Math.ceil(options.alertTimeoutCallMode) || this.ALERT_TIMEOUT_CALL_MODE;
            this.LOGOUT_TIMEOUT_CALL_MODE = Math.ceil(options.logoutTimeoutCallMode) || this.LOGOUT_TIMEOUT_CALL_MODE;
        }
    }

    /**
     *
     * @param {{ activity: '', isManualBehavioral: false }} info
     */
    user.findActivity = (info) => {
        if (info.isFromCaPanel) {
            return user.activities.find(item => item.name == info.activity && item.enrolleeId == info.enrolleeId);
        }
        return user.activities.find(item => (item.name == info.activity) && (item.isBehavioral == info.isManualBehavioral));
    }

    user.switchBhi = (info) => {
        user.broadcast({
            message: 'server:bhi:switch',
            mode: info.isManualBehavioral,
            isCcm: info.isCcm,
            isBehavioral: info.isBehavioral
        })
        user.isBehavioral = info.isManualBehavioral
    }

    user.closeOtherBehavioralActivity = (info, ws) => {
        const activity = user.activities.find(item => (item.name == info.activity) && (item.isBehavioral !== info.isManualBehavioral))
        if (activity) {
            activity.sockets.splice(activity.sockets.indexOf(ws), 1)
        }
    }

    user.start = (info, ws) => {
        /**
         * to be executed when a page is opened
         */
        validateInfo(info)
        validateWebSocket(ws)
        user.totalTime = Math.max(user.totalTime, Number(info.totalTime))
        user.totalCCMTime = Math.max(user.totalCCMTime, Number(info.totalCCMTime))
        user.totalBHITime = Math.max(user.totalBHITime, Number(info.totalBHITime))
        user.enter(info, ws)
        ws.providerId = info.providerId
        ws.patientId = info.patientId
        ws.isFromCaPanel = info.isFromCaPanel;
        let activity = user.findActivity(info)
        if (!!Number(info.initSeconds) && user.allSockets.length <= 1 && activity) {
            /**
             * make sure the page load time is taken into account
             */
            activity.duration += info.initSeconds
        }
        if (user.callMode) {
            ws.send(JSON.stringify({message: 'server:call-mode:enter'}))
        } else {
            ws.send(JSON.stringify({message: 'server:call-mode:exit'}))
        }
        if (user.isBehavioral) {
            ws.send(JSON.stringify({message: 'server:bhi:switch', mode: true}))
        }
    }

    user.enter = (info, ws) => {
        /*
         * to be executed on client:enter when the client focuses on a page
         */
        validateInfo(info)
        validateWebSocket(ws)
        let activity = user.findActivity(info)
        if (!activity) {
            activity = createActivity(info)
            activity.sockets.push(ws)
            user.activities.push(activity)
        } else if (activity) {
            if (activity.sockets.indexOf(ws) < 0) {
                activity.sockets.push(ws)
            }
        }

        user.closeAllModals()

        /**
         * check inactive seconds
         */
        if (user.inactiveSeconds) {
            if (user.inactivityRequiresNoModal()) {
                user.handleNoModal(info)
            } else if (user.inactivityRequiresModal()) {
                if (ws.readyState === ws.OPEN) ws.send(JSON.stringify({message: 'server:modal'}))
            } else {
                user.respondToModal(false, info)
                user.logout()
            }
        }
        ws.active = true

        let enrolleeId = null;
        const activeActivity = user.activities.filter(activity => activity.isActive);
        if (activeActivity.length) {
            enrolleeId = activeActivity[0].enrolleeId;
        }
        $emitter.emit(`server:enter:${user.providerId}`, user.patientId, user.patientFamilyId, enrolleeId)
    }

    /**
     * general logout
     */
    user.logout = () => {
        user.isLoggingOut = true
        user.allSockets.forEach(socket => {
            if (socket.readyState === socket.OPEN) {
                socket.send(JSON.stringify({message: 'server:logout'}))
            }
        })
    }

    /**
     * logout because of mouse and keyboard inactivity while on client page
     * removes about 90 seconds from the duration
     */
    user.clientInactivityLogout = (info) => {
        if (!user.isLoggingOut) {
            user.removeInactiveDuration(info)
        }
        user.logout()
    }

    user.closeAllModals = () => {
        /**
         * inform all clients to close their open inactivity-modal
         */
        user.allSockets.forEach(socket => {
            if (socket.readyState === socket.OPEN) {
                socket.send(JSON.stringify({message: 'server:inactive-modal:close'}))
            }
        })
    }

    user.leave = (ws) => {
        /**
         * to be executed on client:leave when the client page loses focus
         */
        validateWebSocket(ws)
        ws.active = false
    }

    user.exit = (ws) => {
        /**
         * to be executed on ws:close when a WebSocket connection closes
         */
        user.activities.forEach(activity => {
            const index = activity.sockets.findIndex(socket => socket === ws)
            if (index >= 0) {
                activity.sockets.splice(index, 1)
            }
        })
    }

    user.sync = (socket) => {
        user.allSockets.forEach(ws => {
            const shouldSend = socket ? (socket !== ws) : true // if socket arg is specified, don't send to that socket

            const totalCcmSeconds = user.totalCcmSecondsFromCache > user.totalCcmSeconds ? user.totalCcmSecondsFromCache : user.totalCcmSeconds;
            const totalBhiSeconds = user.totalBhiSecondsFromCache > user.totalBhiSeconds ? user.totalBhiSecondsFromCache : user.totalBhiSeconds;
            const totalSeconds = totalBhiSeconds + totalCcmSeconds;

            if (ws.readyState === ws.OPEN && shouldSend) {
                ws.send(JSON.stringify({
                    message: 'server:sync',
                    seconds: totalSeconds,
                    ccmSeconds: totalCcmSeconds,
                    bhiSeconds: totalBhiSeconds
                }))
            }
        })
    }

    user.handleNoModal = (info) => {
        let activity = user.findActivity(info)
        if (activity) {
            activity.duration += user.inactiveSeconds
        }
        user.inactiveSeconds = 0
    }

    /**
     *
     * @param {boolean} response yes/no on whether the practitioner was busy on a patient during calculated inactive-time
     */
    user.respondToModal = (response, info) => {
        let activity = user.findActivity(info)
        if (activity) {
            if (response) {
                activity.duration += user.inactiveSeconds
            } else {
                activity.duration += 30
            }
        }
        user.inactiveSeconds = 0
    }

    user.showInactiveModal = (info, now = () => (new Date())) => {
        let activity = user.findActivity(info)
        if (activity) {
            activity.isInActiveModalShown = true
            activity.inactiveModalShowTime = now()
        }
    }

    user.closeInactiveModal = (info, response, now = () => (new Date())) => {
        let activity = user.findActivity(info)
        if (activity && activity.inactiveModalShowTime) {
            activity.isInActiveModalShown = false
            const elapsedSeconds = (new Date(now() - activity.inactiveModalShowTime)).getSeconds()
            if (response) {
                activity.duration += elapsedSeconds
            } else {
                user.removeInactiveDuration(info)
            }
            activity.inactiveModalShowTime = null
        }
    }

    user.removeInactiveDuration = (info) => {
        let activity = user.findActivity(info)
        if (activity) {
            //the user has been inactive,
            //therefore remove a default of seconds (depending whether call mode or not)
            //or assign 30 seconds. whichever is max.
            //so minimum is 30 seconds.
            const alertTimeout = user.callMode ? user.ALERT_TIMEOUT_CALL_MODE : user.ALERT_TIMEOUT;
            const removedAlertTimeout = activity.duration - (alertTimeout - user.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS);
            activity.duration = Math.max(removedAlertTimeout, user.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS);
            //activity.duration = Math.max((activity.duration - ((!user.callMode ? user.ALERT_TIMEOUT : user.ALERT_TIMEOUT_CALL_MODE) - user.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS)), user.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS)
        }
    }

    const serverEnterHandler = (patientId, patientFamilyId, enrolleeId) => {
        if (!Number(enrolleeId)) {
            //i don't understand this logic
            if (!Number(patientId) || (Number(patientId) && (Number(patientFamilyId) || Number(user.patientFamilyId)) && (patientFamilyId != user.patientFamilyId))) {
                user.exitCallMode(info)
            }
        }
    }

    user.enterCallMode = (info) => {
        let activity = user.findActivity(info)

        if (activity) {
            activity.callMode = true
        }


        user.broadcast({message: 'server:call-mode:enter'})

        $emitter.on(`server:enter:${user.providerId}`, serverEnterHandler)
    }

    user.exitCallMode = (info) => {
        user.activities.forEach(activity => {
            activity.callMode = false
        })

        user.broadcast({message: 'server:call-mode:exit'})

        $emitter.removeListener(`server:enter:${user.providerId}`, serverEnterHandler)
    }

    user.close = () => {
        /**
         * to be executed when all sockets have closed
         * reset everything related to user.
         * see CPM-111 - CCM time isn't showing correctly for the nurse
         */
        user.inactiveSeconds = 0;
        user.totalBHITime = 0;
        user.totalCCMTime = 0;
        user.totalTime = 0;

        //CPM-176 Call mode turns off when switching screens
        //user.activities = [];
        user.activities.forEach(activity => {
            activity.duration = 0
        });

        /*
        user.totalTime += user.activities.reduce((a, b) => a + b.duration, 0)
        user.totalCCMTime += user.activities.filter(activity => !activity.isBehavioral).reduce((a, b) => a + b.duration, 0)
        user.totalBHITime += user.activities.filter(activity => activity.isBehavioral).reduce((a, b) => a + b.duration, 0)

        */
        user.isLoggingOut = null
    }

    user.report = () => ({
        seconds: user.totalSeconds,
        startTime: user.totalTime,
        callMode: user.callMode,
        activities: user.activities.map(activity => ({
            name: activity.name,
            title: activity.title,
            duration: activity.duration,
            url: activity.url,
            url_short: activity.url_short,
            start_time: activity.start_time
        })),
        key: user.key
    })

    user.changeActivity = (info, ws) => {
        if (info.modify) {
            // we allow changing activity name using a filter
            const activity = user.findActivity({activity: info.modifyFilter, enrolleeId: info.enrolleeId, isManualBehavioral: info.isManualBehavioral})
            activity.name = info.activity;
            activity.enrolleeId = info.enrolleeId;
            return;
        }

        user.sendToCpm(false);
        const totalCcm = user.totalCcmSeconds;
        const totalBhi = user.totalBhiSeconds;
        user.activities = [];
        user.totalCCMTime = totalCcm;
        user.totalBHITime = totalBhi;
        user.totalTime = totalCcm + totalBhi;
        user.enter(info, ws);
    };

    user.sendToCpm = (emitLogout) => {
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
                enrolleeId: activity.enrolleeId,
                url: activity.url,
                url_short: activity.url_short,
                start_time: activity.start_time,
                end_time: formatTimeForServer(new Date()),
                is_behavioral: activity.isBehavioral,
                force_skip: activity.forceSkip
            }))
        };

        if (user.totalCcmSeconds === 0 && user.totalBhiSeconds === 0) {
            console.log('will not cache ccm because time is 0');
        } else {
            const currentCache = getTime(user.key);
            if (currentCache && (currentCache.ccm > user.totalCcmSeconds || currentCache.bhi > user.totalBhiSeconds)) {
                console.log('will not cache ccm because cache is higher');
            }
            else {
                console.log('caching ccm', user.totalCcmSeconds);
                storeTime(user.key, requestData.activities, user.totalCcmSeconds, user.totalBhiSeconds);
            }
        }

        axios
            .post(url, requestData)
            .then((response) => {

                console.log(response.status,
                    response.data,
                    requestData.patientId,
                    requestData.activities.map(activity => activity.duration).join(', '));

            })
            .catch((err) => {
                errorLogger.report(err);
                console.error(err)
            });

        if (emitLogout) {
            $emitter.emit('socket:server:logout', requestData)
        }
    };

    return user
}

module.exports = TimeTrackerUser
