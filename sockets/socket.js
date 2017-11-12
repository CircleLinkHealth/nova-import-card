module.exports = app => {
  require("express-ws")(app);

  const axios = require("axios")

  const usersTime = {};

  app.ws("/time", (ws, req) => {
    ws.on("message", (message = "") => {
      try {
        const data = JSON.parse(message);
        /**
                 * {
                 *  "id": 1,
                 *  "patientId": 874
                 * }
                 */
        if (data.constructor.name === "Object") {
          if (
            (!!Number(data.id) || Number(data.id) === 0) &&
            (!!Number(data.patientId) || Number(data.patientId) === 0)
          ) {
            //check that [id] and [patientId] are numbers
            const key = `${data.id}-${data.patientId}`;
            ws.key = key;
            if (data.message === "update") {
              if (!usersTime[key]) {
                if (!data.info || data.info.constructor.name !== 'Object') {
                  /**
                   * Verify that [data.info] is a valid object
                   */
                  ws.send(JSON.stringify({
                    error: 'Invalid data: [data.info] must be a valid object'
                  }))
                  return;
                }
              }
              usersTime[key] = usersTime[key] || {
                seconds: 0,
                dates: [
                  {
                    start: new Date(),
                    end: null
                  }
                ],
                info: data.info,
                sockets: [],
                interval() {
                  //console.log(this.dates)
                  const seconds = Math.floor(this.dates.map(date => {
                    return (date.end || new Date()) - date.start;
                  }).reduce((a, b) => a + b, 0) / 1000);
                  return this.seconds + seconds;
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
                }
              };
              if (usersTime[key].sockets.indexOf(ws) < 0) {
                //add ws to client sockets list
                usersTime[key].sockets.push(ws);
              }
              ws.send(
                JSON.stringify({
                  seconds: usersTime[key].interval(),
                  clients: usersTime[key].sockets.length
                })
              );
            } else if (data.message === "stop") {
              const key = ws.key;
              if (usersTime[key].dates[usersTime[key].dates.length - 1]) usersTime[key].dates[usersTime[key].dates.length - 1].end = new Date();
              usersTime[key].cleanup();
              ws.clientState = "stopped";
              ws.send(
                JSON.stringify({
                  message: "ws stopped"
                })
              );
            } else if (data.message === "start") {
              const key = ws.key;
              if (usersTime[key].dates[usersTime[key].dates.length - 1] && !usersTime[key].dates[usersTime[key].dates.length - 1].end) {
                usersTime[key].dates[usersTime[key].dates.length - 1].end = new Date();
              }
              usersTime[key].dates.push({
                start: new Date(),
                end: null
              });
              usersTime[key].cleanup();
              ws.clientState = null;
              ws.send(
                JSON.stringify({
                  message: "ws started"
                })
              );
            }
          } else {
            console.error("[data.id] is NaN", data.id);
          }
        } else {
          console.error("[data] is not an Object", data);
        }
      } catch (ex) {
        console.error("there was an error parsing", message, ex);
      }
    });

    ws.on("close", ev => {
      const key = ws.key;
      if (key && usersTime[key]) {
        usersTime[key].sockets.splice(usersTime[key].sockets.indexOf(ws), 1);
        if (usersTime[key].sockets.length == 0) {
          if (usersTime[key].info) {
            const info = usersTime[key].info;
            info.totalTime = usersTime[key].seconds * 1000;
            const url = info.submitUrl
            console.log(info, url)

            axios.post(url, info).then((response) => {
              console.log(response.status, response.data)
              //console.log(response)
              delete usersTime[key];
            }).catch((err) => {
              console.error(err)
            })
          }
        }
      }
    });
  });

  setInterval(() => {
    for (const key in usersTime) {
      const listeners = usersTime[key].sockets.filter(
                          socket => socket.clientState !== "stopped"
                        ).length
      console.log(
        "sending message to ",
        listeners,
        " clients", usersTime[key].interval()
      );
      if (listeners === 0 && usersTime[key].dates[usersTime[key].dates.length - 1] && !usersTime[key].dates[usersTime[key].dates.length - 1].end) {
        usersTime[key].dates[usersTime[key].dates.length - 1].end = new Date();
      }
      usersTime[key].sockets.forEach(socket => {
        if (socket.clientState != "stopped") {
          socket.send(
            JSON.stringify({
              seconds: usersTime[key].interval(),
              clients: usersTime[key].sockets.length
            })
          );
        }
      });
    }
  }, 3000);
};
