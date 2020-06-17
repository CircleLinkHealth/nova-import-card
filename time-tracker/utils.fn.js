const validateInfo = (info) => {
    if (!info || !['Object', 'TimeTrackerInfo'].includes(info.constructor.name)) throw new Error('[info] must be a valid object')
}

const createActivity = (info) => {
    validateInfo(info)

    return {
        name: info.activity || 'unknown',
        title: info.title || 'unknown',
        enrolleeId: info.enrolleeId,
        forceSkip: !!info.forceSkip,
        duration: 0,
        url: info.urlFull,
        url_short: info.urlShort,
        start_time: info.startTime,
        sockets: [],
        callMode: false,
        isBehavioral: info.isManualBehavioral || false,
        get isActive() {
            return this.sockets.some(socket => socket.active)
        },
        get hasSockets() {
            return this.sockets.length > 0
        }
    }
}

const addSeconds = (seconds = 0) => () => {
    const d = new Date()
    d.setSeconds(d.getSeconds() + seconds)
    return d
}

const falsy = /^(?:f(?:alse)?|no?|0+)$/i;

/**
 * [false, 'false', 'FALSE', '0', 'undefined', 'NaN', 'null'] => false
 * [true, 'true', 'TRUE', '1'] => true
 * */
function parseBool(val) {
    return !falsy.test(val) && !!val;
}

function parseNumber(val, defaultValue) {
    if (val && !isNaN(+val)) {
        return +val;
    }
    return defaultValue;
}

module.exports.parseBool = parseBool;
module.exports.parseNumber = parseNumber;
module.exports.validateInfo = validateInfo;
module.exports.createActivity = createActivity;
module.exports.addSeconds = addSeconds;
