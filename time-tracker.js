require('./prototypes/date.prototype')
const moment = require('moment')

function TimeTracker(now = () => (new Date())) {
    const users = {}

    this.get = (key, info) => {
        return users[key] = users[key] || this.create(key, info)
    }

    this.create = (key, info) => {
        return new TimeTrackerUser(key, info, now)
    }

    this.users = () => {
        return Object.values(users)
    }

    this.remove = (key) => {
        if (users[key]) delete users[key]
    }

    this.exists = (key) => {
        return !!users[key]
    }

    this.keys = () => {
        return Object.keys(users)
    }

    this.exit = (key) => {
        if (users[key]) {
            const user = users[key]
            if (user.info) {
                user.info.totalTime = Number(user.info.totalTime || 0) + Number(user.seconds)
            }
            user.seconds = 0
        }
    }
}

Array.prototype.last = function () {
    const arr = this
    return arr[arr.length - 1]
}

function TimeTrackerUser(key, info, now = () => (new Date())) {
    /** verify that key is valid string in regex format /\d*-\d*\/ */
    if (!key || key.constructor.name !== 'String' || (key.indexOf('-') < 0)) {
        throw new Error('[key] must be a valid string')
    }

    /** verify that info is a valid object */
    if (!info || info.constructor.name !== 'Object') {
        throw new Error('[info] must be a valid object')
    }

    const user = {
        seconds: 0,
        dates: [],
        key: key,
        info: Object.assign(info, { activities: [] }),
        sockets: [],
        setEndTime(nowFn = now) {
            if (this.dates.last() && !this.dates.last().end) {
                this.dates.last().end = nowFn()
            }
            return this
        },
        slimInterval(nowFn = now) {
            return Math.floor(this.dates.map(date => {
                return (date.end || nowFn()) - date.start;
              }).reduce((a, b) => a + b, 0) / 1000)
        },
        interval(nowFn = now) {
          return this.seconds + this.slimInterval(nowFn);
        },
        cleanup() {
          /**
           * remove objects with start and end data properties from [dates] array
           * so it doesn't get bulky and make .interval() take longer than necessary
           */
          this.seconds += Math.floor(this.dates.filter(date => date.start && date.end)
                                      .map(date => date.end - date.start)
                                      .reduce((a, b) => a + b, 0) / 1000);
          this.dates = this.dates.filter(date => !date.end);

          return this.seconds
        },
        setActivity(_activity) {
            const activity = this.info.activities.find(a => a.name === _activity.name)
            if (activity) {
                activity.duration = (activity.duration || 0) + this.slimInterval()
            }
            else {
                this.info.activities.push({
                    name: _activity.name,
                    url: _activity.urlFull,
                    title: _activity.title,
                    url_short: _activity.urlShort,
                    start_time: moment((this.dates[0] || {}).start || new Date()).format('YYYY-MM-DD hh:mm:ss'),
                    duration: this.slimInterval()
                })
            }
        },
        stop(_activity, nowFn = now) {
          this.setEndTime(nowFn)
          this.setActivity(_activity)
          return this
        },
        resume(nowFn = now) {
            /**
             * used to be START!
             */
            if (!this.dates.last() || this.dates.last().end) {
                this.setEndTime(nowFn)
                this.dates.push({
                    start: nowFn(),
                    end: null
                })
            }
            return this
        },
        setInitSeconds(nowFn = now) {
            this.dates.unshift({
                start: nowFn().addSeconds(0 - (info.initSeconds || 0)),
                end: nowFn()
            })
            return this.dates
        }
    }
    return user.resume()
}

module.exports = TimeTracker;
module.exports.TimeTrackerUser = TimeTrackerUser