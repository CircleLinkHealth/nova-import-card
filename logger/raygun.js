var raygunClient = null;
if (process.env.RAYGUN_API_KEY) {
    var raygun = require('raygun');
    raygunClient = new raygun.Client().init({apiKey: process.env.RAYGUN_API_KEY});
}

module.exports = {
    getRaygun: function () {
        return raygunClient;
    }
};