const assert = require('chai').assert
require('../prototypes/array.prototype')
require('../prototypes/date.prototype')

const TimeTracker = require('../time-tracker')

describe('TimeTracker', () => {
    it('should make an instance of TimeTracker', () => {
        const timeTracker = new TimeTracker()

        assert.isNotNull(timeTracker)
        assert.isDefined(timeTracker)
    })
})