module.exports = app => {
  require('express-ws')(app);

  const TimeTracker = require('../time-tracker')
  const TimeTrackerUser = TimeTracker.TimeTrackerUser

  const axios = require('axios')

  const timeTracker = new TimeTracker()

  const wsErrorHandler = function (err) {
    if (err) console.error("ws-error", err)
  }

  const errorThrow = (err, ws) => {
    console.error(err)
    if (ws) {
      ws.send(JSON.stringify({
        error: err
      }), wsErrorHandler)
    }
  }

  app.ws('/time', (ws, req) => {
    ws.on('message', (message = '') => {
      try {
        const data = JSON.parse(message);
        /**
         * Sample structure of [data] object
         * {
         *  'id': 1,
         *  'patientId': 874,
         *  'message': 'start'
         *  'info': { ... }
         * }
         */
        if (data.constructor.name === 'Object') {
          if (
            (!!Number(data.id) || Number(data.id) === 0) &&
            (!!Number(data.patientId) || Number(data.patientId) === 0)
          ) {
            //check that [id] and [patientId] are numbers
            const key = `${data.id}-${data.patientId}`;
            ws.key = key;
            if (data.message === 'start') {
              try {
                const user = timeTracker.get(key, data.info)
                if (user.info) {
                  user.info.initSeconds = data.info.initSeconds;
                  console.log(user.setInitSeconds())
                }
                if (user.sockets.indexOf(ws) < 0) user.sockets.push(ws);
                user.sockets.forEach(socket => {
                  socket.send(JSON.stringify({
                    message: 'tt:update-previous-seconds',
                    previousSeconds: user.info.totalTime
                  }))
                })
                ws.send(JSON.stringify({
                  message: 'tt:tick',
                  seconds: user.interval(),
                  clients: user.sockets.length
                }), wsErrorHandler)
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            } 
            else if (data.message === 'stop') {
              try {
                const user = timeTracker.get(key)
                const info = (data.info || {})
                user.stop({ 
                  name: info.activity || 'unknown', 
                  urlFull: info.urlFull, 
                  urlShort: info.urlShort 
                })
                user.cleanup()
                ws.clientState = 'stopped'
                ws.send(
                  JSON.stringify({
                    message: 'tt:stopped'
                  }), wsErrorHandler
                )
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            } 
            else if (data.message === 'resume') {
              try {
                const user = timeTracker.get(key, data.info)
                user.resume()
                ws.clientState = null;
                ws.send(
                  JSON.stringify({
                    message: 'tt:resume',
                    seconds: user.interval(),
                    clients: user.sockets.length
                  }), wsErrorHandler
                )
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            }
            else if (data.message === 'inactivity-cancel') {
              try {
                const user = timeTracker.get(key, data.info)
                user.seconds = Math.min(user.seconds, 30)
              }
              catch (ex) {
                errorThrow(ex, ws)
                return;
              }
            }
          } 
          else {
            errorThrow('[data.id] is NaN', ws);
          }
        } 
        else {
          errorThrow('[data] is not an Object', ws);
        }
      } catch (ex) {
        errorThrow(ex, ws);
      } 
    }); 

    ws.on('close', ev => {
      const key = ws.key;
      const user = timeTracker.get(key)
      if (key && user) {
        user.sockets.splice(user.sockets.indexOf(ws), 1);
        if (user.sockets.length == 0) {
          const info = user.info;
          if (info) {
            
            const url = info.submitUrl

            const requestData = Object.assign(Object.assign({}, info), { totalTime: user.cleanup() * 1000 })
            
            console.log(requestData)
            
            timeTracker.exit(key);
  
            axios.post(url, requestData).then((response) => {
              console.log(response.status, response.data)
            }).catch((err) => {
              console.error(err)
            })
          }
          else {
            errorThrow('info.totalTime is undefined ... key is ' + key)
          }
        }
      }
    });
  }); 

  setInterval(() => {
    for (const user of timeTracker.users()) {
      const listeners = user.sockets.filter(
                          socket => socket.clientState !== 'stopped'
                        )

      console.log(
        'sending message to clients:',
        listeners.length,
        'interval:', user.interval(), 'totalTime:', (user.info || {}).totalTime
      );
      user.sockets.forEach(socket => {
        if (socket.clientState != 'stopped') {
          socket.send(
            JSON.stringify({
              message: 'tt:tick',
              seconds: user.interval(),
              clients: user.sockets.length
            }), wsErrorHandler
          );
        }
      });
    }
  }, 3000);
 
};
