const {EventEmitter} = require('events')
const {validateInfo} = require('./utils.fn')
const TimeTrackerUser = require('./time-tracker.user')
const raygunClient = require('../logger/raygun').getRaygun();

function TimeTracker($emitter = new EventEmitter()) {
    const users = {}

    this.key = (info) => `${info.providerId}-${info.patientId}`

    this.validateInfo = validateInfo

    this.get = (info) => {

        this.validateInfo(info)

        const key = this.key(info)

        if (info.activity === '') {
            info.activity = 'unknown'
            reportError(new Error('activity has no name'), info);
        }

        return users[key] = users[key] || this.create(info)
    }

    this.create = (info) => {

        this.validateInfo(info)

        return new TimeTrackerUser(info, $emitter)
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

function reportError(error, customData, onDone) {
    if (raygunClient) {
        raygunClient.send(error, customData, onDone ? onDone : function () {
        });
    }
    else {
        console.error(error.message, customData);
    }
}

module.exports = TimeTracker
module.exports.TimeTrackerUser = TimeTrackerUser