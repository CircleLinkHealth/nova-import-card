const validateInfo = (info) => {
    if (!info || !['Object', 'TimeTrackerInfo'].includes(info.constructor.name)) throw new Error('[info] must be a valid object')
}

module.exports.validateInfo = validateInfo