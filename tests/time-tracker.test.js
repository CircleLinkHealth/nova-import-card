const {
    assert,
    TimeTracker
} = require('./setup.test')

describe('TimeTracker', () => {
    it('should make an instance of TimeTracker', () => {
        const timeTracker = new TimeTracker()

        assert.isNotNull(timeTracker)
        assert.isDefined(timeTracker)
    })
})