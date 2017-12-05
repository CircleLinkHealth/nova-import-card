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
        if (!ws || ws.constructor.name !== 'WebSocket') throw new Error('[ws] must be a valid WebSocket instance')
    }

    const getActivity = (info) => {
        validateInfo(info)

        return { 
            name: info.activity || 'unknown', 
            title: info.title || 'unknown',
            duration: 0,
            urlFull: info.urlFull, 
            urlShort: info.urlShort,
            sockets: []
        }
    }
    
    const user = {
        key: key,
        inactiveSeconds: 0, //inactive time in seconds
        activities: []
    }

    user.enter = (info, ws) => {
        /**
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
         * to be executed on client:leave when the client leaves a page
         */
        validateWebSocket(ws)
        ws.active = false
    }

    return user
}

module.exports = TimeTracker;
module.exports.TimeTrackerUser = TimeTrackerUser