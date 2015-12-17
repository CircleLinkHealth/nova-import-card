module.exports = (function (input, kind) {
    var date, batch;
    var list = [];
    if (kind == 'encounters') {
        batch = bb.data.encounters;
    } else if (kind == 'procedures') {
        batch = bb.data.procedures;
    } else if (kind == 'problems') {
        batch = bb.data.problems;
    } else if (kind == 'immunizations') {
        batch = bb.data.immunizations;
    } else if (kind == 'medications') {
        batch = bb.data.medications;
        return [];
    } else if (kind == 'labs') {
        batch = [];
        for (var m in bb.data.results) {
            for (var l in bb.data.results[m].results) {
                batch.push(bb.data.results[m].results[l]);
            }
        }
    }
    if (input.date) {
        if (input.date instanceof Date) {
            dates = [input.date.toDateString()];
        } else {
            dates = [input.date_range.start.toDateString(), input.date_range.end.toDateString()];
        }
        for (var k in batch) {
            if (typeof k == "number") {
                target = batch[k];
                if (target.date instanceof Date) {
                    target_date = [target.date.toDateString()];
                } else {
                    target_dates = [target.date_range.start.toDateString, target.date_range.end.toDateString()];
                }
                if (filters.intersects(dates, target_dates).length > 0) {
                    list.push(target);
                }
            }
        }
    }
    return list;
});