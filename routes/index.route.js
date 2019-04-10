var express = require('express');
var router = express.Router();

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
    var raygunClient = require('../logger/raygun').getRaygun();
    router.get('/', (req, res) => {
        res.send({
            message: 'Time Tracker',
            uptime: Math.floor(process.uptime()),
            raygun: raygunClient !== null
        })
    })
}

module.exports = router;
