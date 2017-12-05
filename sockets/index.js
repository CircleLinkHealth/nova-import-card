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
          if (data.info) {
            if (data.message === 'client:start') {
              try {
                const info = data.info
                const user = app.getTimeTracker(info).get(info)
                user.start(info)
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
                user.leave(info)
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
                user.enter(info)
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
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
      const key = ws.key;
      const user = timeTracker.exists(key) ? timeTracker.get(key) :
                  (timeTrackerNoLiveCount.exists(key) ? timeTrackerNoLiveCount.get(key) : null)
      if (key && user) {
        user.sockets.splice(user.sockets.indexOf(ws), 1);
        if (user.sockets.length == 0) {
          //no active sessions
          const info = user.info;
          if (info) {
            info.away = null

            const url = info.submitUrl

            const requestData = Object.assign(Object.assign({}, info), { totalTime: user.cleanup() * 1000 })
            
            timeTracker.exit(key);
            timeTrackerNoLiveCount.exit(key);
  
            axios.post(url, requestData).then((response) => {
              console.log(response.status, response.data, requestData)
            }).catch((err) => {
              console.error(err)
            })

            info.activities = []
          }
          else {
            errorThrow('info.totalTime is undefined ... key is ' + key)
          }
        }
      }
    });
  }); 

  setInterval(() => {
    for (const user of [...timeTracker.users(), ...timeTrackerNoLiveCount.users()]) {
      const listeners = user.sockets.filter(
                          socket => socket.clientState !== 'stopped'
                        )

      console.log(
        'sending message to clients:',
        listeners.length,
        'interval:', user.interval(), 
        'totalTime:', (user.info || {}).totalTime,
        'live-count:', user.info.noLiveCount ? '(no)' : '(yes)'
      );
      user.sockets.forEach(socket => {
        if (socket.clientState != 'stopped') {
          if (socket.readyState === socket.OPEN) {
            socket.send(
              JSON.stringify({
                message: 'tt:tick',
                seconds: user.interval(),
                clients: user.sockets.length
              }), wsErrorHandler
            )
          }
        }
      });
    }
  }, 3000);
 
};

/**
 * restart process every day
 */

setTimeout(function () {
  process.exit(0)
}, 24 * 60 * 60 * 1000)