const assert = require('chai').assert

const TimeTracker = require('../time-tracker')
const TimeTrackerUser = TimeTracker.TimeTrackerUser

const WebSocket = require('./stubs/ws.stub')

const TimeTrackerInfo = require('./stubs/time-tracker-info.stub')
const info = new TimeTrackerInfo({
    chargeableServices: [
        {total_time: 0, chargeable_service: {id: 1, code: 'CPT 99490', display_name: 'CCM'}},
        {total_time: 0, chargeable_service: {id: 2, code: 'CPT 99484', display_name: 'BHI'}},
    ],
    chargeableServiceId: 1
});
const key = (new TimeTrackerInfo()).createKey()
const ws = new WebSocket()
const {addSeconds} = require('../time-tracker/utils.fn')

module.exports = {
    assert,
    TimeTracker,
    TimeTrackerUser,
    TimeTrackerInfo,
    WebSocket,
    info,
    key,
    ws,
    addSeconds
}
