let raygunClient = null;
if (process.env.RAYGUN_API_KEY) {
    const raygun = require('raygun');
    raygunClient = new raygun.Client().init({apiKey: process.env.RAYGUN_API_KEY});
    raygunClient.user = function (req) {
        return req ? req.userId : undefined;
    }
}

module.exports = {
    getRaygun: function () {
        return raygunClient;
    }
};