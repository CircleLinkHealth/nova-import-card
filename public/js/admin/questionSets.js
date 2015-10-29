$(document).ready(function(){
    $('#filterProgram').select2();
    $('#filterQsType').select2();
    $('#filterQuestion').select2();


    /*
    $( "#filterSubmit" ).click(function() {
        var str = $( "#filters :input" ).serialize();
        alert(str);
        var action = $( "#filterForm" ).attr( "action" );
        str = (action + '?' + str);
        alert( str );
        $( "#filterform" ).prop( 'action',  str);
        var result = $( "#filterForm" ).attr( "action" );
        alert( result );
        return false;
    });
    */

});