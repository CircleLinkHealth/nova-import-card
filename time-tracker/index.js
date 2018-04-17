require('../prototypes/date.prototype')
require('../prototypes/array.prototype')

const { validateInfo } = require('./utils.fn')
const TimeTrackerUser = require('./time-tracker.user')

function TimeTracker(now = () => (new Date())) {
    const users = {}

    this.key = (info) => `${info.providerId}-${info.patientId}`

    this.validateInfo = validateInfo

    this.get = (info) => {

        this.validateInfo(info)

        const key = this.key(info)

        if (info.activity === '') {
            info.activity = 'unknown'
        }

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

module.exports = TimeTracker
module.exports.TimeTrackerUser = TimeTrackerUser