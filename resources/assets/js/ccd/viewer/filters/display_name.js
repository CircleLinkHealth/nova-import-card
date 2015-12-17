module.exports = (function (input) {
    if (input.given instanceof Array) {
        return input.call_me ? input.call_me : input.given[0];
    } else {
        return input.call_me ? input.call_me : input.given;
    }
});