$(document).ready(function(){

    // condition
    $('body').on('click', '.add-condition', function(event) {
        event.preventDefault();
        addCondition();
        return false;
    });

    $('body').on('click', '.remove-condition', function(event) {
        event.preventDefault();
        var count = $(this).attr('count');
        $('#c' + count).remove();
        return false;
    });


    // action
    $('body').on('click', '.add-action', function(event) {
        event.preventDefault();
        addAction();
        return false;
    });

    $('body').on('click', '.remove-action', function(event) {
        event.preventDefault();
        var count = $(this).attr('count');
        $('#a' + count).remove();
        return false;
    });
});

function addCondition() {
    // get html
    var conditionshtml = $('#jsconditions').html();

    conditionCount = rulesGetRulesCountForType('conditions');

    // increment ids
    conditionshtml = conditionshtml.replace("c*action", "c" + conditionCount + "action");
    conditionshtml = conditionshtml.replace("c*operator", "c" + conditionCount + "operator");
    conditionshtml = conditionshtml.replace("c*value", "c" + conditionCount + "value");
    conditionshtml = conditionshtml.replace("c*count", "" + conditionCount + "");
    conditionshtml = conditionshtml.replace("c*count", "" + conditionCount + ""); // 2nd
    conditionshtml = conditionshtml.replace("c*count", "" + conditionCount + ""); // 3rd

    // append
    $('#conditions').append(conditionshtml);
    return false;
}

function addAction() {
    // get html
    var actionshtml = $('#jsactions').html();
    // count checkboxes to get the number already out (dont forget the extra one in jsactions!)
    var actionCount = ($('.a-condition').length - 1); // (length starts at 1, php arr 0)

    // fix duplicates
    if(conditionCount > 0) {
        var matches = [];
        $("input[name='conditions[]']:checked").each(function () {
            count = this.value;
            if (count.length == 1) {
                matches.push(this.value);
                console.log(count.length);
            }
        });
        // sort descending, so matches[0] is highest
        matches.sort(function (a, b) {
            return b - a
        });
        console.log(matches);
        // use this, will never cause conflict
        conditionCount = parseInt(matches[0])+1;
    }

    actionCount = rulesGetRulesCountForType('actions');

    // increment ids
    actionshtml = actionshtml.replace("a*action", "c" + actionCount + "action");
    actionshtml = actionshtml.replace("a*operator", "c" + actionCount + "operator");
    actionshtml = actionshtml.replace("a*value", "c" + actionCount + "value");
    actionshtml = actionshtml.replace("a*count", "" + actionCount + "");
    actionshtml = actionshtml.replace("a*count", "" + actionCount + ""); // 2nd
    actionshtml = actionshtml.replace("a*count", "" + actionCount + ""); // 3rd

    // append
    $('#actions').append(actionshtml);
    return false;
}

function rulesGetRulesCountForType(type) {
    // count checkboxes to get the number already out (dont forget the extra one in jsactions!)
    var typeCount = ($("input[name='" + type + "[]']").length - 1); // (length starts at 1, php arr 0)

    // fix duplicates
    if(typeCount > 0) {
        var matches = [];
        $("input[name='" + type + "[]']:checked").each(function () {
            count = this.value;
            if (count.length == 1) {
                matches.push(this.value);
                console.log(count.length);
            }
        });
        // sort descending, so matches[0] is highest
        matches.sort(function (a, b) {
            return b - a
        });
        console.log(matches);
        // use this, will never cause conflict
        typeCount = parseInt(matches[0])+1;
        return typeCount;
    } else {
        return 0;
    }
}