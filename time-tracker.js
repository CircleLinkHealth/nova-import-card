require('./prototypes/date.prototype')
require('./prototypes/array.prototype')
const moment = require('moment')

function TimeTracker(now = () => (new Date())) {
    const users = {}

    this.key = (info) => `${info.providerId}-${info.patientId}`

    this.validateInfo = (info) => {
        if (!info || info.constructor.name !== 'Object') throw new Error('[info] must be a valid object')
    }

    this.get = (info) => {

        this.validateInfo(info)

        const key = this.key(info)

        return users[key] = users[key] || this.create(info)
    }

    this.create = (info) => {
        
        this.validateInfo(info)

        return new TimeTrackerUser(info, now)
    }

    this.users = () => {
        return Object.values(users)
    }

    this.remove = (info) => {
        
        this.validateInfo(info)

        const key = this.key(info)

        if (users[key]) delete users[key]
    }

    this.exists = (info) => {
        
        this.validateInfo(info)
        
        const key = this.key(info)

        return !!users[key]
    }

    this.keys = () => {
        return Object.keys(users)
    }
}

function TimeTrackerUser(info, now = () => (new Date())) {

    if (!info || info.constructor.name !== 'Object') throw new Error('[info] must be a valid object')

    const key = `${info.providerId}-${info.patientId}`

    const validateInfo = (info) => {
        if (!info || info.constructor.name !== 'Object') throw new Error('[info] must be a valid object')
    }
    
    const validateWebSocket = (ws) => {
        if (!ws) throw new Error('[ws] must be a valid WebSocket instance')
    }

    const getActivity = (info) => {
        validateInfo(info)

        return { 
            name: info.activity || 'unknown', 
            title: info.title || 'unknown',
            duration: 0,
            url: info.urlFull, 
            url_short: info.urlShort,
            start_time: info.startTime,
            sockets: [],
            get isActive() {
                return this.sockets.some(socket => socket.active)
            }
        }
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
        get totalSeconds() {
            return this.activities.reduce((a, b) => a + b.duration, 0) + info.totalTime
        },
        get allSockets() {
            return this.activities.map(activity => activity.sockets).reduce((a, b) => a.concat(b), [])
        }
    }

    user.start = (info, ws) => {
        /**
         * to be executed when a page is opened
         */
        validateInfo(info)
        validateWebSocket(ws)
        user.enter(info, ws)
        ws.providerId = info.providerId
        ws.patientId = info.patientId
        let activity = user.activities.find(item => item.name == info.activity)
        if (!!Number(info.initSeconds)) {
            /**
             * make sure the page load time is taken into account
             */
            activity.duration += info.initSeconds
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
            activity = getActivity(info)
            user.activities.push(activity)
        }
        if (activity.sockets.indexOf(ws) < 0) {
            activity.sockets.push(ws)
        }
        ws.active = true
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

    return user
}

module.exports = TimeTracker;
module.exports.TimeTrackerUser = TimeTrackerUser