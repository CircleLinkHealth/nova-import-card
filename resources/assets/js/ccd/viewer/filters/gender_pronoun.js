module.exports = (function (input, possessive, absolute) {
    if (input == "female") {
        return possessive ? (absolute ? "hers" : "her") : "she";
    } else {
        return possessive ? "his" : "he";
    }
});