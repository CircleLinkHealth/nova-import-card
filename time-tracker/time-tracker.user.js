const { EventEmitter } = require('events')
const { validateInfo, createActivity } = require('./utils.fn')

function TimeTrackerUser(info, $emitter = new EventEmitter()) {
    
    validateInfo(info)

    const key = `${info.providerId}-${info.patientId}`
    
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
        programId: info.programId,
        ipAddr: info.ipAddr,
        totalTime: info.totalTime,
        totalCCMTime: info.totalCCMTime,
        totalBHITime: info.totalBHITime,
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
         * 
         * @param {any} data JSON or string you want to send via web sockets
         * @param {*} socket WebSocket instance you want to exclude from broadcast
         */
        broadcast (data, socket) {
            this.allSockets.forEach(ws => {
                const shouldSend = socket ? (socket !== ws) : true // if socket arg is specified, don't send to that socket
                if (ws.readyState === ws.OPEN && shouldSend) {
                    ws.send(JSON.stringify(data))
                }
            })
        },
        inactivityRequiresNoModal () {
            return this.inactiveSeconds < (!this.callMode ? this.ALERT_TIMEOUT : this.ALERT_TIMEOUT_CALL_MODE) // 2 minutes if !call-mode and 15 minutes if in call-mode (120, 900)
        },
        inactivityRequiresModal () {
            return !this.inactivityRequiresNoModal() && this.inactiveSeconds < (!this.callMode ? this.LOGOUT_TIMEOUT : this.LOGOUT_TIMEOUT_CALL_MODE) // 10 minutes if !call-mode and 20 minutes if in call-mode (600, 1200)
        },
        inactivityRequiresLogout () {
            return !this.inactivityRequiresModal() && !this.inactivityRequiresNoModal()
        },
        ALERT_TIMEOUT: 120,
        LOGOUT_TIMEOUT: 600,
        ALERT_TIMEOUT_CALL_MODE: 900,
        LOGOUT_TIMEOUT_CALL_MODE: 1200,
        overrideTimeouts (options = {}) {
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
        return user.activities.find(item => (item.name == info.activity) && (item.isBehavioral == info.isManualBehavioral))
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
        user.totalTime = Math.max(user.totalTime, info.totalTime)
        user.totalCCMTime = Math.max(user.totalCCMTime, info.totalCCMTime)
        user.totalBHITime = Math.max(user.totalBHITime, info.totalBHITime)
        user.enter(info, ws)
        ws.providerId = info.providerId
        ws.patientId = info.patientId
        let activity = user.findActivity(info)
        if (!!Number(info.initSeconds) && user.allSockets.length <= 1 && activity) {
            /**
             * make sure the page load time is taken into account
             */
            activity.duration += info.initSeconds
        }
        if (user.callMode) {
            ws.send(JSON.stringify({ message: 'server:call-mode:enter' }))
        }
        else {
            ws.send(JSON.stringify({ message: 'server:call-mode:exit' }))
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
        }
        else if (activity) {
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
                user.respondToModal(true)
            }
            else if (user.inactivityRequiresModal()) {
                if (ws.readyState === ws.OPEN) ws.send(JSON.stringify({ message: 'server:modal' }))
            }
            else {
                user.respondToModal(false)
                user.logout()
            }
        }
        ws.active = true

        $emitter.emit(`server:enter:${user.providerId}`, user.patientId, user.patientFamilyId)
    }

    /**
     * general logout
     */
    user.logout = () => {
        user.isLoggingOut = true
        user.allSockets.forEach(socket => {
            if (socket.readyState === socket.OPEN) {
                socket.send(JSON.stringify({ message: 'server:logout' }))
            }
        })
    }

    /**
     * logout because of mouse and keyboard inactivity while on client page
     * removes about 90 seconds from the duration
     */
    user.clientInactivityLogout = () => {
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
                socket.send(JSON.stringify({ message: 'server:inactive-modal:close' }))
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
            if (ws.readyState === ws.OPEN && shouldSend) {
                ws.send(JSON.stringify({ message: 'server:sync', seconds: user.totalSeconds, ccmSeconds: user.totalCcmSeconds, bhiSeconds: user.totalBhiSeconds }))
            }
        })
    }

    /**
     * 
     * @param {boolean} response yes/no on whether the practitioner was busy on a patient during calculated inactive-time
     */
    user.respondToModal = (response) => {
        let activity = user.findActivity(info)
        if (activity) {
            if (response) {
                activity.duration += user.inactiveSeconds
            }
            else {
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
            }
            else {
                user.removeInactiveDuration(info)
            }
            activity.inactiveModalShowTime = null
        }
    }

    user.removeInactiveDuration = (info) => {
        let activity = user.findActivity(info)
        if (activity) {
            activity.duration = Math.max((activity.duration - ((!user.callMode ? 120 : 900) - 30)), 30)
        }
    }

    const serverEnterHandler = (patientId, patientFamilyId) => {
        if (!Number(patientId) || (Number(patientId) && (Number(patientFamilyId) || Number(user.patientFamilyId)) && (patientFamilyId != user.patientFamilyId))) {
            user.exitCallMode(info)
        }
    }

    user.enterCallMode = (info) => {
        let activity = user.findActivity(info)
        
        if (activity) {
            activity.callMode = true
        }
        

        user.broadcast({ message: 'server:call-mode:enter' })

        $emitter.on(`server:enter:${user.providerId}`, serverEnterHandler)
    }

    user.exitCallMode = (info) => {
        user.activities.forEach(activity => {
            activity.callMode = false
        })

        user.broadcast({ message: 'server:call-mode:exit' })

        $emitter.removeListener(`server:enter:${user.providerId}`, serverEnterHandler)
    }

    user.close = () => {
        /**
         * to be executed when all sockets have closed
         */
        user.inactiveSeconds = 0
        user.totalTime += user.activities.reduce((a, b) => a + b.duration, 0)
        user.activities.forEach(activity => {
            activity.duration = 0
        })
        user.isLoggingOut = null
    }

    user.report = () => ({
        seconds: user.totalSeconds,
        startTime: user.totalTime,
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

    return user
}

module.exports = TimeTrackerUser