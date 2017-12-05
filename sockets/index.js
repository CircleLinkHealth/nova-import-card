module.exports = app => {
  require('express-ws')(app);

  const TimeTracker = require('../time-tracker')
  const TimeTrackerUser = TimeTracker.TimeTrackerUser

  const axios = require('axios')

  const timeTracker = new TimeTracker()
  const timeTrackerNoLiveCount = new TimeTracker()

  app.timeTracker = timeTracker
  app.timeTrackerNoLiveCount = timeTrackerNoLiveCount
  app.getTimeTracker = (info) => {
    if (info && info.noLiveCount) return timeTrackerNoLiveCount
    else return timeTracker
  }

  const wsErrorHandler = function (err) {
    if (err) console.error("ws-error", err)
  }

  const errorThrow = (err, ws) => {
    console.error(err)
    if (ws) {
      if (ws.readyState === ws.OPEN) {
        ws.send(JSON.stringify({
          error: err
        }), wsErrorHandler)
      }
    }
  }

  app.ws('/time', (ws, req) => {
    ws.on('message', (message = '') => {
      try {
        const data = JSON.parse(message);
        /**
         * Sample structure of [data] object
         * {
         *  'message': 'client:start'
         *  'info': { ... }
         * }
         */
        if (data.constructor.name === 'Object') {
          if (data.info || data.message === 'PING') {
            if (data.message === 'client:start') {
              try {
                const info = data.info
                const user = app.getTimeTracker(info).get(info)
                user.start(info, ws)
                user.sync()
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            } 
            else if (data.message === 'client:leave') {
              try {
                const info = data.info
                const user = app.getTimeTracker(info).get(info)
                user.leave(ws)
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            }
            else if (data.message === 'client:enter') {
              try {
                const info = data.info
                const user = app.getTimeTracker(info).get(info)
                user.enter(info, ws)
                user.sync()
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            }
            else if (data.message === 'PING') {

            }
            else {
              errorThrow(new Error('invalid message'), ws)
            }
          } 
          else {
            errorThrow('[data.info] must be a valid Object', ws);
          }
        } 
        else {
          errorThrow('[data] must be a valid Object', ws);
        }
      } catch (ex) {
        errorThrow(ex, ws);
      } 
    }); 
 
    ws.on('close', ev => {
      const keyInfo = { patientId: ws.patientId, providerId: ws.providerId }
      const user = timeTracker.exists(keyInfo) ? timeTracker.get(keyInfo) :
                  (timeTrackerNoLiveCount.exists(keyInfo) ? timeTrackerNoLiveCount.get(keyInfo) : null)
      if (user) {
        user.exit(ws)
        if (user.allSockets.length == 0) {
          //no active sessions
          const url = user.url
          
          const requestData = {
            patientId: user.patientId,
            providerId: user.providerId,
            ipAddr: user.ipAddr,
            programId: user.programId,
            activities: user.activities
          }

          axios.post(url, requestData).then((response) => {
            console.log(response.status, response.data, requestData)
          }).catch((err) => {
            console.error(err)
          })

          
        }
      }
    });
  }); 

  setInterval(() => {
    for (const user of [...timeTracker.users(), ...timeTrackerNoLiveCount.users()]) {
      user.activities.forEach(activity => {
        if (activity.isActive) {
          activity.duration += 1;
        }
      })

      console.log(
        'activities:', user.activities.filter(activity => activity.isActive).length, 
        'totalSeconds:', user.totalSeconds
      )
    }
  }, 1000);
 
};

/**
 * restart process every day
 */

setTimeout(function () {
  process.exit(0)
}, 24 * 60 * 60 * 1000)