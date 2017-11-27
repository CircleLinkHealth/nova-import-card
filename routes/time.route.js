var express = require('express');
var router = express.Router();

if (!app) throw new Error('express app should be globally accessible')

if (!app.timeTracker) throw new Error('app.timeTracker should be an instance of TimeTracker')

router.use(function (req, res, next) {
  const allowedOrigins = [ 'https://cpm-web.dev', 'https://staging.careplanmanager.com', 'https://careplanmanager.com' ]
  const origin = req.headers.origin
  if (allowedOrigins.indexOf(origin) >= 0) res.header('Access-Control-Allow-Origin', origin)
  res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, X-CSRF-TOKEN")
  next()
})

/* GET provider-patient time. */
router.get('/:providerId/:patientId', function(req, res, next) {

  const providerId = req.params.providerId
  const patientId = req.params.patientId

  const timeTracker = app.timeTracker

  const key = providerId + '-' + patientId

  const userExists = timeTracker.exists(key)

  

  if (userExists) {
    const user = timeTracker.get(key)
    if (user) res.send({
      seconds: user.interval(),
      totalTime: user.interval() + user.info.totalTime,
      info: user.info,
      key: user.key
    })
    else res.status(404).send({
      error: 'user not found'
    })
  }
  else {
    res.status(404).send({
      error: 'user key not found'
    })
  }
});

router.get('/keys', function (req, res, next) {
  const timeTracker = app.timeTracker

  res.send(timeTracker.keys())
})

module.exports = router;
