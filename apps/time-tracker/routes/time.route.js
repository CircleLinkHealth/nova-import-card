const userCache = require('../cache/user-time');

var express = require('express');
var router = express.Router();

if (!app) throw new Error('express app should be globally accessible')

if (!app.timeTracker) throw new Error('app.timeTracker should be an instance of TimeTracker')

router.use(function (req, res, next) {
    const allowedOrigins = ['https://cpm-web.dev', 'https://staging.careplanmanager.com', 'https://careplanmanager.com']
    const origin = req.headers.origin
    if (allowedOrigins.indexOf(origin) >= 0) res.header('Access-Control-Allow-Origin', origin)
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, X-CSRF-TOKEN")
    next()
})

const userExistsValidator = function (req, res, next) {
    const providerId = req.params.providerId
    const patientId = req.params.patientId

    const timeTracker = app.timeTracker

    const info = {providerId, patientId}

    const userExists = timeTracker.exists(info)

    if (userExists) next()
    else res.status(404).send({
        error: 'user key not found'
    })
}

const addTimeToChargeableServiceValidator = function (req, res, next) {
    const data = req.body;
    if (!data || Object.keys(data) < 4 || isNaN(data.chargeable_service_id) || isNaN(data.duration_seconds)) {
        res.status(404).send({
            error: 'invalid request:' + JSON.stringify(data)
        })
        return;
    }

    next();
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
router.put('/:providerId/:patientId', userExistsValidator, addTimeToChargeableServiceValidator, function (req, res, next) {
    const providerId = req.params.providerId
    const patientId = req.params.patientId

    const timeTracker = app.timeTracker

    const info = {providerId, patientId}

    const user = timeTracker.get(info)

    userCache.clearForPatient(+patientId);

    if (user) {
        const data = req.body;
        user.addToChargeableService(+data.chargeable_service_id, data.chargeable_service_code, data.chargeable_service_name, +data.duration_seconds);
        res.send(user.report())
    } else res.status(404).send({
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
router.get('/:providerId/:patientId', userExistsValidator, function (req, res, next) {

    const providerId = req.params.providerId
    const patientId = req.params.patientId

    const timeTracker = app.timeTracker

    const info = {providerId, patientId}

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

router.get('/active', function (req, res, next) {
    const timeTracker = app.timeTracker

    const result = timeTracker.users()
        .filter(x => x.totalSeconds > 0)
        .map(x => {
            return {
                report: x.report(),
                totalSeconds: x.totalSeconds,
                isActive: x.isActive
            }
        });
    res.send(result);
})

router.get('/active-no-live-count', function (req, res, next) {
    const timeTracker = app.timeTrackerNoLiveCount

    const result = timeTracker.users()
        .filter(x => x.totalSeconds > 0)
        .map(x => {
            return {
                report: x.report(),
                totalSeconds: x.totalSeconds,
                isActive: x.isActive
            }
        });
    res.send(result);
})

router.get('/cache', function (req, res, next) {
    res.send(userCache.getAll());
});

router.put('/cache/:patientId/clear', function (req, res, next) {
    const patientId = req.params.patientId;
    userCache.clearForPatient(+patientId);
    res.send('ok');
});

router.get('/', (req, res) => {
    res.send({message: 'Time Tracker'})
})

module.exports = router;
