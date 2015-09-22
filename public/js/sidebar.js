$(document).ready(function(){
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        //$('#sidebar-wrapper').height($("#wrapper").height());
        $("#wrapper").toggleClass("toggled",600);
    });
    $("#menu-toggle-2").click(function(e) {
        e.preventDefault();
        //$('#sidebar-wrapper').height($("#wrapper").height());
        $("#wrapper").toggleClass("toggled-2",600);
        var i = 1;
        var timer2 = setInterval(function() {
            //$('#page-content-wrapper').hide();
            if( $('#sidebar-wrapper').width() != 50 && $('#sidebar-wrapper').width() != 250 ) {
                //alert( i + ',2,-' + $('#sidebar-wrapper').width())
                i++
            } else {
                window.dispatchEvent(new Event('resize'));
                //alert('found it on timer2 boom');
                clearInterval(timer2)
                //$('#page-content-wrapper').show();
            }
            if( i == 25 ) {
                clearInterval(timer2)
            }
            }, 50
        );
    });

    initMenu();
    //$('#sidebar-wrapper').height($("#wrapper").height());
});
function initMenu() {
    $('#menu ul').hide();
    $('#menu ul').children('.current').parent().show();
    //$('#menu ul:first').show();
    $('#menu li a').click(
        function() {
            var checkElement = $(this).next();
            if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
                return false;
            }
            if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
                $('#menu ul:visible').slideUp('normal');
                checkElement.slideDown('normal');
                return false;
            }
        }
    );
}