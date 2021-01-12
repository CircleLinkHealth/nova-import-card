const swaggerJSDoc = require('swagger-jsdoc');

const package = require('../package.json')

// swagger definition
const swaggerDefinition = {
    info: {
      title: 'time-tracker',
      version: package.version,
      description: package.description,
    },
    host: app.url,
    basePath: '/api/',
};
  
// options for the swagger docs
const options = {
// import swaggerDefinitions
swaggerDefinition: swaggerDefinition,
// path to the API docs
apis: ['./routes/**/*.js'],
};

// initialize swagger-jsdoc
const swaggerSpec = swaggerJSDoc(options);

module.exports = swaggerSpec