function addCondition() {
    var conditionshtml = $('#jsconditions').html();
    $('#conditions').append(conditionshtml);
    return false;
}

function addAction() {
    var conditionshtml = $('#jsconditions').html();
    $('#actions').append(conditionshtml);
    return false;
}

$(document).ready(function(){
    $('body').on('click', '.add-condition', function(event) {
        event.preventDefault();
        addCondition();
        return false;
    });

    $('body').on('click', '.add-action', function(event) {
        event.preventDefault();
        addAction();
        return false;
    });
});