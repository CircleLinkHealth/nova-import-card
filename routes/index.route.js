var express = require('express');
var router = express.Router();

const parseBool = require("../time-tracker/utils.fn").parseBool;

const raygunClient = require('../logger/raygun').getRaygun();

if (process.env.NODE_ENV !== 'production') {
    const swaggerSpec = require('../swagger')

    router.get('/swagger.json', function (req, res) {
        res.setHeader('Content-Type', 'application/json');
        res.send(swaggerSpec);
    });

    /* GET home page. */
    require('express-swagger-ui')({
        app: app,
        swaggerUrl: '/swagger.json',  // this is the default value
        localPath: '/'       // this is the default value
    });
}
else {
    router.get('/', (req, res) => {
        res.send({
            message: 'Time Tracker',
            uptime: Math.floor(process.uptime()),
            raygun: raygunClient !== null
        })
    });
}

if (parseBool(process.env.ENABLE_MONITORING_TEST_ROUTE)) {
    router.get('/report', (req, res) => {
        if (raygunClient) {
            raygunClient.send(new Error("test"), {}, function () {
                res.send({message: "done"});
            });
        }
        else {
            res.send({message: "raygun client not found"});
        }
    });
}

module.exports = router;
