var express = require('express');
var router = express.Router();

const swaggerSpec = require('../swagger')

router.get('/swagger.json', function(req, res) {
    res.setHeader('Content-Type', 'application/json');
    res.send(swaggerSpec);
});

/* GET home page. */
require('express-swagger-ui')({
  app       : app,
  swaggerUrl: '/swagger.json',  // this is the default value 
  localPath : '/'       // this is the default value 
});

module.exports = router;
