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

function addPadding(v) {
    return v.toString().length < 2 ? `0${v}` : v;
}

//four digit year
function getYear(d) {
    return d.getFullYear();
}

//getMonth: 0 - 11
function getMonth(d) {
    return addPadding(d.getMonth() + 1);
}

//getDate: 1 - 31
function getDate(d) {
    return addPadding(d.getDate());
}

//getHours: 0 - 23
function getHours(d) {
    return addPadding(d.getHours());
}

//getMinutes: 0 - 59
function getMinutes(d) {
    return addPadding(d.getMinutes());
}

//getSeconds: 0 - 59
function getSeconds(d) {
    return addPadding(d.getSeconds());
}

function getTime(d) {
    return `${getHours(d)}:${getMinutes(d)}:${getSeconds(d)}`;
}

function formatTimeForServer(dateTime) {
    return `${getYear(dateTime)}-${getMonth(dateTime)}-${getDate(dateTime)} ${getTime(dateTime)}`;
}

module.exports.parseBool = parseBool;
module.exports.parseNumber = parseNumber;
module.exports.validateInfo = validateInfo;
module.exports.createActivity = createActivity;
module.exports.addSeconds = addSeconds;
module.exports.formatTimeForServer = formatTimeForServer;
