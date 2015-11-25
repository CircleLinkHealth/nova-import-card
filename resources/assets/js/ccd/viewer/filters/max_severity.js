module.exports = (function (input) {

    function isInt(input) {
        return parseInt(input, 10) % 1 === 0;
    }

    var i, mild = 0, moderate = 0, severe = 0, exists = 0;
    if (input.severity) {
        if (input.severity.match(/severe/i)) {
            severe++;
        } else if (input.severity.match(/moderate/i)) {
            moderate++;
        } else if (input.severity.match(/mild/i)) {
            mild++;
        } else {
            if (input.name) {
                exists++;
            }
        }
    } else {
        for (i in input) {
            if (isInt(i)) {
                if (input[i].severity) {
                    if (input[i].severity.match(/severe/i)) {
                        severe++;
                    } else if (input[i].severity.match(/moderate/i)) {
                        moderate++;
                    } else if (input[i].severity.match(/mild/i)) {
                        mild++;
                    } else {
                        if (input[i].name) {
                            exists++;
                        }
                    }
                } else {
                    if (input.name) {
                        exists++;
                    }
                }
            }
        }
    }
    if (severe) {
        return severe > 1 ? "multiple severe" : "severe";
    } else if (moderate) {
        return moderate > 1 ? "multiple moderate" : "moderate";
    } else if (mild) {
        return mild > 1 ? "multiple mild" : "mild";
    } else {
        return exists === 0 ? "no" : exists > 1 ? "multiple" : "";
    }
});