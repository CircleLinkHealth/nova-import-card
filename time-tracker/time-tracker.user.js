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
        noLiveCount: info.noLiveCount,
        patientFamilyId: info.patientFamilyId,
        get totalSeconds() {
            return this.activities.reduce((a, b) => a + b.duration, 0) + this.totalTime
        },
        get allSockets() {
            return this.activities.map(activity => activity.sockets).reduce((a, b) => a.concat(b), [])
        },
        get callMode() {
            return this.activities.reduce((a, b) => (a || b.callMode), false)
        },
        broadcast (data, socket) {
            this.allSockets.forEach(ws => {
                const shouldSend = socket ? (socket !== ws) : true // if socket arg is specified, don't send to that socket
                if (ws.readyState === ws.OPEN && shouldSend) {
                    ws.send(JSON.stringify(data))
                }
            })
        },
        inactivityRequiresNoModal () {
            return this.inactiveSeconds < (!this.callMode ? 120 : 900) // 2 minutes if !call-mode and 15 minutes if in call-mode
        },
        inactivityRequiresModal () {
            return !this.inactivityRequiresNoModal() && this.inactiveSeconds < (!this.callMode ? 600 : 1200) // 10 minutes if !call-mode and 20 minutes if in call-mode
        },
        inactivityRequiresLogout () {
            return !this.inactivityRequiresModal() && !this.inactivityRequiresNoModal()
        }
    }

    user.start = (info, ws) => {
        /**
         * to be executed when a page is opened
         */
        validateInfo(info)
        validateWebSocket(ws)
        user.enter(info, ws)
        //user.totalTime = Math.max(user.totalTime, info.totalTime)
        ws.providerId = info.providerId
        ws.patientId = info.patientId
        let activity = user.activities.find(item => item.name === info.activity)
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
        let activity = user.activities.find(item => item.name == info.activity)
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
                user.respondToModal()
                user.allSockets.forEach(socket => {
                    if (socket.readyState === socket.OPEN) {
                        socket.send(JSON.stringify({ message: 'server:logout' }))
                    }
                })
            }
        }
        ws.active = true

        $emitter.emit(`server:enter:${user.providerId}`, user.patientId, user.patientFamilyId)
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
                ws.send(JSON.stringify({ message: 'server:sync', seconds: user.totalSeconds }))
            }
        })
    }

    /**
     * 
     * @param {boolean} response yes/no on whether the practitioner was busy on a patient during calculated inactive-time
     */
    user.respondToModal = (response) => {
        let activity = user.activities.find(item => item.name == info.activity)
        if (response) {
            activity.duration += user.inactiveSeconds
        }
        else {
            activity.duration += 30
        }
        user.inactiveSeconds = 0
    }
    
    user.showInactiveModal = (info, now = () => (new Date())) => {
        let activity = user.activities.find(item => item.name === info.activity)
        if (activity) {
            activity.isInActiveModalShown = true
            activity.inactiveModalShowTime = now()
        }
    } 
    
    user.closeInactiveModal = (info, response, now = () => (new Date())) => {
        let activity = user.activities.find(item => item.name === info.activity)
        if (activity && activity.inactiveModalShowTime) {
            activity.isInActiveModalShown = false
            const elapsedSeconds = (new Date(now() - activity.inactiveModalShowTime)).getSeconds()
            if (response) {
                activity.duration += elapsedSeconds
            }
            else {
                activity.duration = Math.max((activity.duration - 90), 0)
            }
            activity.inactiveModalShowTime = null
        }
    }

    const serverEnterHandler = (patientId, patientFamilyId) => {
        if (!Number(patientId) || (Number(patientId) && (Number(patientFamilyId) || Number(user.patientFamilyId)) && (patientFamilyId != user.patientFamilyId))) {
            user.exitCallMode(info)
        }
    }

    user.enterCallMode = (info) => {
        let activity = user.activities.find(item => item.name === info.activity)
        activity.callMode = true

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