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

const userExistsValidator = function (req, res, next) {
  const providerId = req.params.providerId
  const patientId = req.params.patientId

  const timeTracker = app.timeTracker

  const info = { providerId, patientId }

  const userExists = timeTracker.exists(info)

  if (userExists) next()
  else res.status(404).send({
    error: 'user key not found'
  })
}

/**
 * @swagger
 * /:practitionerId/:patientId:
 *   put:
 *     tags:
 *       - Default
 *     requestBody:
 *      content:
 *        application/json:
 *          examples:
 *            startTime: 200
 *     description: Modifies latent properties in practiioner-patient time activities, such as adding to startTime
 *     produces:
 *       - application/json
 *     responses:
 *       200:
 *         description: practitioner-patient activities
 */
router.put('/:providerId/:patientId', userExistsValidator, function (req, res, next) {
  const providerId = req.params.providerId
  const patientId = req.params.patientId

  const timeTracker = app.timeTracker

  const info = { providerId, patientId }
  
  const user = timeTracker.get(info)

  if (user) {
    const {
      startTime
    } = req.body
  
    if (Number(startTime)) {
      user.totalTime += Number(startTime)
    }
    res.send(user.report())
  }
  else res.status(404).send({
    error: 'user not found'
  })
})

/**
 * @swagger
 * /:practitionerId/:patientId:
 *   get:
 *     tags:
 *       - Default
 *     description: Returns a practitioner-patient time activities
 *     produces:
 *       - application/json
 *     responses:
 *       200:
 *         description: practitioner-patient activities
 */
router.get('/:providerId/:patientId', userExistsValidator, function(req, res, next) {

  const providerId = req.params.providerId
  const patientId = req.params.patientId

  const timeTracker = app.timeTracker

  const info = { providerId, patientId }

  const user = timeTracker.get(info)
  if (user) res.send(user.report())
  else res.status(404).send({
    error: 'user not found'
  })
});

/**
 * @swagger
 * /keys:
 *   get:
 *     tags:
 *       - Default
 *     description: Returns a list of practitionerId-patientId keys currently active
 *     produces:
 *       - application/json
 *     responses:
 *       200:
 *         description: list of practitionerId-patientId keys currently active
 */
router.get('/keys', function (req, res, next) {
  const timeTracker = app.timeTracker

  res.send(timeTracker.keys())
})

router.get('/', (req, res) => {
  res.send({ message: 'Time Tracker' })
})

module.exports = router;
