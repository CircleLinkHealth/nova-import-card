const validateInfo = (info) => {
    if (!info || !['Object', 'TimeTrackerInfo'].includes(info.constructor.name)) throw new Error('[info] must be a valid object')
}

const createActivity = (info) => {
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

module.exports.validateInfo = validateInfo
module.exports.createActivity = createActivity