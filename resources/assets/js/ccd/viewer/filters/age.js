module.exports = (function (date) {
    var today = new Date();
    var ms = today - date;
    var years = ms / (1000 * 60 * 60 * 24 * 365);
    return Math.floor(years);
});