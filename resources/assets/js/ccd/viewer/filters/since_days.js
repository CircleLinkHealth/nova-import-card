module.exports = (function (input, days) {
    var batch = [];
    var today = new Date();
    var target_date = new Date(today.setDate(today.getDate() - days));
    for (var k in input) {
        if (isInt(k)) {
            if (input[k].effective_time && input[k].effective_time.low && input[k].effective_time.low > target_date) {
                batch.push(input[k]);
            } else if (input[k].date && input[k].date > target_date) {
                batch.push(input[k]);
            }
        }
    }
    return batch;
});